import {afterAll, beforeAll, describe, expect, it} from "@jest/globals";
import {Helper} from "./helper";
import {ElementHandle} from "puppeteer";

describe("crawl some blog with smartphone", () => {
  let c: Helper;
  let captcha_key: string = "1234";
  const start_url = "http://localhost:8080/testblog2/";

  beforeAll(async () => {
    c = new Helper();
    await c.init();
    await c.setSpUserAgent();
  });

  it("open blog top", async () => {
    await c.openUrl(start_url);
    await c.getSS("blog_top_sp.png");
    expect(await c.page.title()).toEqual("testblog2");
  });

  it("blog top - check cookie", async () => {
    const cookies = await c.page.cookies();

    // ここがコケるということは、テスト無しにクッキーが追加されているかも
    expect(cookies.length).toEqual(1);

    const [session_cookie] = cookies.filter((elm) => elm.name === "dojima");

    // console.log(session_cookie);
    expect(session_cookie.domain).toEqual("localhost");
    expect(session_cookie.path).toEqual("/");
    expect(session_cookie.expires).toEqual(-1);
    expect(session_cookie.httpOnly).toEqual(true);
    expect(session_cookie.secure).toEqual(false);
    expect(session_cookie.session).toEqual(true);
    expect(session_cookie.sameSite).toEqual("Lax");
  });

  it("blog top - check fuzzy display contents", async () => {
    const title_text = await c.getTextBySelector("h1 a");
    expect(title_text).toEqual("testblog2");

    const entry_bodies = await c.page.$$eval("ul#entry_list strong", (elm_list) => {
      let bodies = [];
      elm_list.forEach((elm) => bodies.push(elm.textContent));
      return bodies;
    });
    // console.log(entry_bodies);

    expect(entry_bodies[0].match(/3rd/)).toBeTruthy();
    expect(entry_bodies[1].match(/2nd/)).toBeTruthy();
    expect(entry_bodies[2].match(/テスト記事１/)).toBeTruthy();
  });

  it("blog top - click first entry", async () => {
    const response = await c.clickBySelector("ul#entry_list a");
    await c.getSS("entry_sp.png");
    expect(response.url()).toEqual(start_url + "blog-entry-3.html");
    expect(await c.page.title()).toEqual("3rd - testblog2");

    const entry_h2_title = await c.getTextBySelector("div.entry_title h2");
    expect(entry_h2_title.match(/3rd/)).toBeTruthy();

    const entry_body = await c.getTextBySelector("div.entry_body");
    expect(entry_body.match(/3rd/)).toBeTruthy();
  });

  it("entry page - click next page", async () => {
    const response = await c.clickBySelector("a.nextpage");
    expect(response.url()).toEqual(start_url + "blog-entry-2.html");
    expect(await c.page.title()).toEqual("2nd - testblog2");

    const entry_h2_title = await c.getTextBySelector("div.entry_title h2");
    expect(entry_h2_title.match(/2nd/)).toBeTruthy();

    const entry_body = await c.getTextBySelector("div.entry_body");
    expect(entry_body.match(/2nd/)).toBeTruthy();
  });

  it("entry page - click prev page", async () => {
    const response = await c.clickBySelector("a.prevpage");

    expect(response.url()).toEqual(start_url + "blog-entry-3.html");
    expect(await c.page.title()).toEqual("3rd - testblog2");

    const entry_h2_title = await c.getTextBySelector("div.entry_title h2");
    expect(entry_h2_title.match(/3rd/)).toBeTruthy();

    const entry_body = await c.getTextBySelector("div.entry_body");
    expect(entry_body.match(/3rd/)).toBeTruthy();
  });

  let posted_comment_num;

  it("entry page - open comment form", async () => {
    const response = await c.clickBySelector("#entry > ul > li:nth-child(1) > a");

    expect(response.url()).toEqual(start_url + "blog-entry-3.html?m2=form");
    expect(await c.page.title()).toEqual("3rd - testblog2");
  });

  let post_comment_title;

  it("user template comment form - fill comment", async () => {
    // generate uniq title
    post_comment_title = "テストタイトル_" + Math.floor(Math.random() * 1000000).toString();
    // console.log(post_comment_title);

    await c.getSS("comment_before_fill_sp");
    await fillCommentForm(
      c.page,
      "テスト太郎",
      post_comment_title,
      "test@example.jp",
      "http://example.jp",
      "これはテスト投稿です\nこれはテスト投稿です！",
      "pass_is_pass"
    )
    await c.getSS("comment_after_fill_sp");

    const response = await c.clickBySelector("#comment_post > form > div > a");
    await c.getSS("comment_confirm_sp");

    expect(response.url()).toEqual(start_url);
  });

  it("comment form - failed with wrong captcha", async () => {
    // input wrong captcha
    await c.page.type("input[name=token]", "0000"); // wrong key

    const response = await c.clickBySelector("#sys-comment-form > fieldset > div > input");
    await c.getSS("comment_wrong_captcha_sp");

    expect(response.url()).toEqual(start_url);

    // 特定しやすいセレクタがない
    const is_captcha_failed = await c.page.$$eval("p", (elms) => {
      let isOk = false;
      elms.forEach((elm) => {
        if (elm.textContent.match(/認証に失敗しました/)) {
          isOk = true;
        }
      });
      return isOk;
    });

    expect(is_captcha_failed).toBeTruthy();
  });

  it("comment form - comment success", async () => {
    await c.page.type("input[name=token]", captcha_key);
    await c.getSS("comment_correct_token_sp");

    const response = await c.clickBySelector("#sys-comment-form input[type=submit]");
    const exp = new RegExp(
      start_url + 'index.php\\?mode=entries&process=view&id=[0-9]{1,100}'
    );
    expect(response.url().match(exp)).not.toBeNull();

    const comment_a_text = await c.getTextBySelector("#entry > ul > li:nth-child(2) > a");
    await c.getSS("comment_success_sp");
    posted_comment_num = parseInt(comment_a_text.match(/コメント\(([0-9]{1,3})\)/)[1]);
  });

  it("entry page - open comment list", async () => {
    const response = await c.clickBySelector("#entry > ul > li:nth-child(2) > a");
    expect(response.url()).toEqual(start_url + "blog-entry-3.html?m2=res");
    expect(await c.page.title()).toEqual("3rd - testblog2");
  });

  it("comment list - open comment form", async () => {
    const response = await c.clickElement(await getEditLinkByTitle(post_comment_title));
    expect(response.url()).toEqual(expect.stringContaining(start_url + "index.php?mode=entries&process=comment_edit&id="));
    expect(await c.page.title()).toEqual("コメントの編集 - testblog2");
    await c.getSS("comment_edit_before_sp");
  });

  it("user template comment form - comment edit", async () => {
    await fillEditForm(
      c.page,
      "テスト太郎2",
      "edited_" + post_comment_title,
      "test2@example.jp",
      "http://example.jp/2",
      "これは編集済み",
      "pass_is_pass"
    )
    await c.getSS("comment_edit_filled_sp");

    // comment formへ遷移
    let response = await c.clickBySelector("#comment_post > form > div > input[type=submit]:nth-child(1)");
    await c.getSS("comment_edit_confirm_sp");
    expect(response.status()).toEqual(200);

    await c.page.type("input[name=token]", captcha_key);

    // 送信
    response = await c.clickBySelector("#sys-comment-form > fieldset > div > input");
    await c.getSS("comment_edit_success_sp");
    expect(response.status()).toEqual(200);
  });

  it("comment list - test to fail delete", async () => {
    const response = await c.clickElement(await c.page.$("#entry > ul > li:nth-child(2) > a"));
    expect(response.url()).toEqual(start_url + "blog-entry-3.html?m2=res");
    expect(await c.page.title()).toEqual("3rd - testblog2");
    await c.getSS("comment_list_delete_before_sp");
  });

  it("comment list - test to fail delete", async () => {
    await c.getSS("comment_form_delete_before1_sp");
    const response = await c.clickElement(await getEditLinkByTitle("edited_" + post_comment_title));
    await c.getSS("comment_form_delete_before2_sp");
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(expect.stringContaining(start_url + "index.php?mode=entries&process=comment_edit&id="));
    expect(await c.page.title()).toEqual("コメントの編集 - testblog2");
  });

  it("user template comment form - delete fail by wrong password", async () => {
    const delete_button = await c.page.$(
      "#comment_post > form > div > input[type=submit]:nth-child(2)"
    );

    const [response] = await Promise.all([
      c.waitLoad(),
      await delete_button.click()
    ]);

    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url);

    const wrong_password_error_text = await c.page.$eval("#comment_post > form > dl > dd:nth-child(12) > p", elm => elm.textContent);
    expect(/必ず入力してください/.exec(wrong_password_error_text)).toBeTruthy();
  });

  it("entry page - open comment list to delete", async () => {
    await c.openUrl(start_url + "blog-entry-3.html");

    const response = await c.clickElement(await c.page.$("#entry > ul > li:nth-child(2) > a"));
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url + "blog-entry-3.html?m2=res");
    expect(await c.page.title()).toEqual("3rd - testblog2");
    await c.getSS("comment_list_delete_before_sp");
  });

  it("comment list - open comment form to delete", async () => {
    await c.getSS("comment_form_delete_before1_sp");
    const response = await c.clickElement(await getEditLinkByTitle("edited_" + post_comment_title));
    await c.getSS("comment_form_delete_before2_sp");
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(expect.stringContaining(start_url + "index.php?mode=entries&process=comment_edit&id="));
    expect(await c.page.title()).toEqual("コメントの編集 - testblog2");
  });

  it("user template comment form - comment delete success", async () => {
    // do delete.
    await c.page.type("#comment_post > form > dl > dd:nth-child(12) > input[type=password]", "pass_is_pass");

    const response = await c.clickBySelector("#comment_post > form > div > input[type=submit]:nth-child(2)")
    expect(response.url()).toEqual(start_url + "index.php?mode=entries&process=view&id=3&sp");
  });

  it("entry page - open entry check delete complete", async () => {
    await c.openUrl(start_url);

    const response = await c.clickElement(await c.page.$("#entry_list > li:nth-child(1) > a"));

    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url + "blog-entry-3.html");
    expect(await c.page.title()).toEqual("3rd - testblog2");
  });

  it("entry page - check comment count", async () => {
    const comment_a_text = await c.page.$eval("#entry > ul > li:nth-child(2) > a", elm => elm.textContent);
    const comment_num = parseInt(comment_a_text.match(/コメント\(([0-9]{1,3})\)/)[1]);
    // expect(comment_num).toEqual(posted_comment_num-1); // パラレルでテストが実行されるので、数を数えても正しくできない
  });

  afterAll(async () => {
    await c.browser.close();
  });

  // ========================

  async function getEditLinkByTitle(title): Promise<ElementHandle> {
    // 該当するタイトルの編集リンクを探す
    // SPのコメント一覧専用
    const dt_elm_list = await c.page.$$("#comment dt");
    let idx = -1;
    for (let i = 0; i < dt_elm_list.length; i++) {
      let txt = await (await dt_elm_list[i].getProperty('innerHTML')).jsonValue();
      // console.log(txt);
      if (txt == title) {
        idx = i;
        break;
      }
    }

    if (idx == -1) {
      throw new Error("notfound target title");
    }

    const dd_elm_list = await c.page.$$("#comment dd");
    const target_dd = dd_elm_list[idx];
    // console.log(await target_dd.jsonValue());

    return await target_dd.$("a[title='コメントの編集']")
  }

  async function fillCommentForm(
    page,
    name,
    title,
    email,
    url,
    body,
    pass = "",
  ) {
    await page.$eval(
      "form[name=form1] input[name='comment[name]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "form[name=form1] input[name='comment[name]']",
      name
    );
    await page.$eval(
      "form[name=form1] input[name='comment[title]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "form[name=form1] input[name='comment[title]']",
      title
    );
    await page.$eval(
      "form[name=form1] input[name='comment[mail]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "form[name=form1] input[name='comment[mail]']",
      email
    );
    await page.$eval(
      "form[name=form1] input[name='comment[url]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "form[name=form1] input[name='comment[url]']",
      url
    );
    await page.$eval(
      "form[name=form1] textarea[name='comment[body]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "form[name=form1] textarea[name='comment[body]']",
      body
    );
    await page.$eval(
      "form[name=form1] input[name='comment[pass]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "form[name=form1] input[name='comment[pass]']",
      pass
    );
  }

  async function fillEditForm(
    page,
    name,
    title,
    email,
    url,
    body,
    pass = "",
  ) {
    await page.$eval(
      "#comment_post input[name='edit[name]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "#comment_post input[name='edit[name]']",
      name
    );
    await page.$eval(
      "#comment_post input[name='edit[title]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "#comment_post input[name='edit[title]']",
      title
    );
    await page.$eval(
      "#comment_post input[name='edit[mail]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "#comment_post input[name='edit[mail]']",
      email
    );
    await page.$eval(
      "#comment_post input[name='edit[url]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "#comment_post input[name='edit[url]']",
      url
    );
    await page.$eval(
      "#comment_post textarea[name='edit[body]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "#comment_post textarea[name='edit[body]']",
      body
    );
    await page.$eval(
      "#comment_post input[name='edit[pass]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "#comment_post input[name='edit[pass]']",
      pass
    );
  }
});
