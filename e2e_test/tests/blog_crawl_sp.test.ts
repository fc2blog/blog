import {afterAll, beforeAll, describe, expect, it} from "@jest/globals";
import {Helper} from "./helper";
import {ElementHandle} from "puppeteer";

describe("crawl some blog sp", () => {
  let c: Helper;
  let captcha_key: string;

  beforeAll(async () => {
    c = new Helper();
    await c.init();
    await c.setSpUserAgent();
    captcha_key = "1234";
  });

  const start_url = "http://localhost:8080/testblog2/";

  it("open blog top", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.goto(start_url),
    ]);

    await c.getSS("blog_top_sp.png");

    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url);
    expect(await c.page.title()).toEqual("testblog2");
  });

  it("check cookie", async () => {
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

  it("check fuzzy display contents", async () => {
    const title_text = await c.page.$eval("h1 a", (elm) => elm.innerHTML);
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

  it("click first entry", async () => {
    const link = await c.page.$("ul#entry_list a");

    const [response] = await Promise.all([c.waitLoad(), link.click()]);

    await c.getSS("entry_sp.png");

    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url + "?no=3");
    expect(await c.page.title()).toEqual("3rd - testblog2");

    const entry_h2_title = await c.page.$eval(
      "div.entry_title h2",
      (elm) => elm.textContent
    );
    const entry_body = await c.page.$eval(
      "div.entry_body",
      (elm) => elm.textContent
    );

    expect(entry_body.match(/3rd/)).toBeTruthy();
    expect(entry_h2_title.match(/3rd/)).toBeTruthy();
  });

  it("next page", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.click("a.nextpage"),
    ]);

    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url + "?no=2");
    expect(await c.page.title()).toEqual("2nd - testblog2");

    const entry_h2_title = await c.page.$eval(
      "div.entry_title h2",
      (elm) => elm.textContent
    );
    const entry_body = await c.page.$eval(
      "div.entry_body",
      (elm) => elm.textContent
    );

    expect(entry_body.match(/2nd/)).toBeTruthy();
    expect(entry_h2_title.match(/2nd/)).toBeTruthy();
  });

  it("prev page", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.click("a.prevpage"),
    ]);

    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url + "?no=3");
    expect(await c.page.title()).toEqual("3rd - testblog2");

    const entry_h2_title = await c.page.$eval(
      "div.entry_title h2",
      (elm) => elm.textContent
    );
    const entry_body = await c.page.$eval(
      "div.entry_body",
      (elm) => elm.textContent
    );

    expect(entry_body.match(/3rd/)).toBeTruthy();
    expect(entry_h2_title.match(/3rd/)).toBeTruthy();
  });

  let posted_comment_num;

  it("open comment form", async () => {
    const link = await c.page.$("#entry > ul > li:nth-child(1) > a");
    const [response] = await Promise.all([c.waitLoad(), link.click()]);
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url + "?no=3&m2=form");
    expect(await c.page.title()).toEqual("3rd - testblog2");
  });

  let post_comment_title;

  it("fill comment", async () => {
    // generate uniq title
    post_comment_title = "テストタイトル_" + Math.floor(Math.random() * 1000000).toString();
    console.log(post_comment_title);

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

    const [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#comment_post > form > div > a"),
    ]);

    await c.getSS("comment_confirm_sp");

    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url);
  });

  it("failed with wrong captcha", async () => {
    // input wrong captcha
    await c.page.type("input[name=token]", "0000"); // wrong key

    const [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#sys-comment-form > fieldset > div > input"),
    ]);

    await c.getSS("comment_wrong_captcha_sp");

    expect(response.status()).toEqual(200);
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

  it("comment success", async () => {
    await c.page.type("input[name=token]", captcha_key);
    await c.getSS("comment_correct_token_sp");

    const [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#sys-comment-form input[type=submit]"),
    ]);

    expect(response.status()).toEqual(200);
    const exp = new RegExp(
      start_url + 'index.php\\?mode=entries&process=view&id=[0-9]{1,100}'
    );

    expect(response.url().match(exp)).not.toBeNull();

    const comment_a_text = await c.page.$eval("#entry > ul > li:nth-child(2) > a", elm => elm.textContent);

    await c.getSS("comment_success_sp");
    posted_comment_num = parseInt(comment_a_text.match(/コメント\(([0-9]{1,3})\)/)[1]);
  });

  it("open comment list", async () => {
    const link = await c.page.$("#entry > ul > li:nth-child(2) > a");
    const [response] = await Promise.all([
      c.waitLoad(),
      link.click()
    ]);
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url + "?no=3&m2=res");
    expect(await c.page.title()).toEqual("3rd - testblog2");
  });

  it("open comment form", async () => {
    const link = await getEditLinkByTitle(post_comment_title);
    const [response] = await Promise.all([
      c.waitLoad(),
      link.click()
    ]);
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(expect.stringContaining(start_url + "index.php?mode=entries&process=comment_edit&id="));
    expect(await c.page.title()).toEqual("- testblog2"); // TODO issue #223
    await c.getSS("comment_edit_before_sp");
  });

  it("comment edit", async () => {
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

    // 保存する
    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#comment_post > form > div > input[type=submit]:nth-child(1)"),
    ]);

    await c.getSS("comment_edit_confirm_sp");
    expect(response.status()).toEqual(200);

    await c.page.type("input[name=token]", captcha_key);

    [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#sys-comment-form > fieldset > div > input"),
    ]);

    await c.getSS("comment_edit_success_sp");
    expect(response.status()).toEqual(200);
  });

  it("open comment list to fail delete", async () => {
    const link = await c.page.$("#entry > ul > li:nth-child(2) > a");
    const [response] = await Promise.all([
      c.waitLoad(),
      link.click()
    ]);
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url + "?no=3&m2=res");
    expect(await c.page.title()).toEqual("3rd - testblog2");
    await c.getSS("comment_list_delete_before_sp");
  });

  it("open comment form to fail delete", async () => {
    await c.getSS("comment_form_delete_before1_sp");
    const link = await getEditLinkByTitle("edited_" + post_comment_title);
    const [response] = await Promise.all([
      c.waitLoad(),
      link.click()
    ]);
    await c.getSS("comment_form_delete_before2_sp");
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(expect.stringContaining(start_url + "index.php?mode=entries&process=comment_edit&id="));
    expect(await c.page.title()).toEqual("- testblog2"); // TODO issue #223
  });

  it("comment delete fail by wrong password", async () => {
    const delete_button = await c.page.$(
      "#comment_post > form > div > input[type=submit]:nth-child(2)"
    );

    const [response] = await Promise.all([c.waitLoad(), await delete_button.click()]);

    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url);

    const wrong_password_error_text = await c.page.$eval("#comment_post > form > dl > dd:nth-child(12) > p", elm => elm.textContent);
    expect(/必ず入力してください/.exec(wrong_password_error_text)).toBeTruthy();
  });

  it("open comment list to delete", async () => {
    await Promise.all([
      c.waitLoad(),
      c.page.goto(start_url + "?no=3"),
    ]);

    const link = await c.page.$("#entry > ul > li:nth-child(2) > a");
    const [response] = await Promise.all([
      c.waitLoad(),
      link.click()
    ]);
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url + "?no=3&m2=res");
    expect(await c.page.title()).toEqual("3rd - testblog2");
    await c.getSS("comment_list_delete_before_sp");
  });

  it("open comment form to delete", async () => {
    await c.getSS("comment_form_delete_before1_sp");
    const link = await getEditLinkByTitle("edited_" + post_comment_title);
    const [response] = await Promise.all([
      c.waitLoad(),
      link.click()
    ]);
    await c.getSS("comment_form_delete_before2_sp");
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(expect.stringContaining(start_url + "index.php?mode=entries&process=comment_edit&id="));
    expect(await c.page.title()).toEqual("- testblog2"); // TODO issue #223
  });

  it("comment delete success", async () => {
    // do delete.
    await c.page.type("#comment_post > form > dl > dd:nth-child(12) > input[type=password]", "pass_is_pass");

    const [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#comment_post > form > div > input[type=submit]:nth-child(2)")
    ]);

    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url + "index.php?mode=entries&process=view&id=3&sp");
  });

  it("open entry check delete complete", async () => {
    let [response1] = await Promise.all([
      c.waitLoad(),
      c.page.goto(start_url),
    ]);
    expect(response1.status()).toEqual(200);
    const link = await c.page.$("#entry_list > li:nth-child(1) > a");
    const [response] = await Promise.all([
      c.waitLoad(),
      link.click()
    ]);
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url + "?no=3");
    expect(await c.page.title()).toEqual("3rd - testblog2");
  });

  it("check comment count", async () => {
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
    const dt_elm_list = await c.page.$$("#comment dt");
    let idx = -1;
    for (let i = 0; i < dt_elm_list.length; i++) {
      let txt = await (await dt_elm_list[i].getProperty('innerHTML')).jsonValue();
      console.log(txt);
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
    console.log(await target_dd.jsonValue());

    const edit_link = await target_dd.$("a[title='コメントの編集']")
    return edit_link;
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
