import {afterAll, beforeAll, describe, expect, it} from "@jest/globals";
import {Helper} from "./helper";
import {ElementHandle} from "puppeteer";

// captcha を完全に動的にするには、ページ内から読み込まれる画像リクエストのヘッダーをいじらないといけないので一旦保留
// import {sprintf} from 'sprintf-js';
//
// // https://developer.mozilla.org/ja/docs/Web/JavaScript/Reference/Global_Objects/Math/random
// function getRandomInt(min:number, max:number) :number{
//     min = Math.ceil(min);
//     max = Math.floor(max);
//     return Math.floor(Math.random() * (max - min + 1)) + min; //The maximum is inclusive and the minimum is inclusive
// }
//
// function generateRandomCaptchaKey():string{
//     return sprintf("%04d", getRandomInt(1000,9999));
// }
// await c.page.setExtraHTTPHeaders({
//     "x-debug-force-captcha-key": captcha_key
// });

describe("crawl some blog", () => {
  let c: Helper;
  let captcha_key: string = "1234";
  const start_url = "http://localhost:8080/testblog2/";

  beforeAll(async () => {
    c = new Helper();
    await c.init();
  });

  it("open blog top", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.goto(start_url),
    ]);

    await c.getSS("blog_top");
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url);
    expect(await c.page.title()).toEqual("testblog2");
  });

  it("check cookie", async () => {
    const cookies = await c.page.cookies();

    // ここがコケるということは、テスト無しにクッキーが追加されているかも
    expect(cookies.length).toEqual(2);

    const [session_cookie] = cookies.filter((elm) => elm.name === "dojima");
    const [template_cookie] = cookies.filter(
      (elm) => elm.name === "template_blog_fc2"
    );

    // console.log(session_cookie);
    expect(session_cookie.domain).toEqual("localhost");
    expect(session_cookie.path).toEqual("/");
    expect(session_cookie.expires).toEqual(-1);
    expect(session_cookie.httpOnly).toEqual(true);
    expect(session_cookie.secure).toEqual(false);
    expect(session_cookie.session).toEqual(true);
    expect(session_cookie.sameSite).toEqual("Lax");

    // console.log(template_cookie);
    expect(template_cookie.value).toEqual("glid");
    expect(template_cookie.domain).toEqual("localhost");
    expect(template_cookie.path).toEqual("/");
    // cookieは30日後に失効するように発行されているが、テスト時に正確に一致を期待してはいけないので前後60秒の誤差を許す
    const cookie_fuzz_lower =
      Math.floor(new Date().getTime() / 1000) + (30 * 24 * 60 * 60) - 60;
    const cookie_fuzz_higher =
      Math.floor(new Date().getTime() / 1000) + (30 * 24 * 60 * 60) + 60;
    expect(template_cookie.expires).toBeGreaterThan(cookie_fuzz_lower);
    expect(template_cookie.expires).toBeLessThan(cookie_fuzz_higher);
    expect(template_cookie.httpOnly).toEqual(false);
    expect(template_cookie.secure).toEqual(false);
    expect(template_cookie.session).toEqual(false);
    expect(template_cookie.sameSite).toEqual("Lax");
  });

  it("check fuzzy display contents", async () => {
    const title_text = await c.page.$eval("h1 a", (elm) => elm.innerHTML);
    expect(title_text).toEqual("testblog2");

    const entry_bodies = await c.page.$$eval("div.entry_body", (elm_list) => {
      let bodies = [];
      elm_list.forEach((elm) => bodies.push(elm.textContent));
      return bodies;
    });

    expect(entry_bodies[0].match(/3rd/)).toBeTruthy();
    expect(entry_bodies[1].match(/2nd/)).toBeTruthy();
    expect(entry_bodies[2].match(/テスト記事１/)).toBeTruthy();
  });

  it("click first entry's 「続きを読む」", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      (await c.page.$("div.entry_body a")).click()
    ]);

    await c.getSS("entry");
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url + "?no=3");
    expect(await c.page.title()).toEqual("3rd - testblog2");

    const entry_h2_title = await c.page.$eval(
      "h2.entry_header",
      (elm) => elm.textContent
    );
    const entry_body = await c.page.$eval(
      "div.entry_body",
      (elm) => elm.textContent
    );

    expect(entry_body.match(/3rd/)).toBeTruthy();
    expect(entry_h2_title.match(/3rd/)).toBeTruthy();
  });

  it("click next page", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.click("a.nextentry"),
    ]);

    await c.getSS("entry_next_page");
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url + "?no=2");
    expect(await c.page.title()).toEqual("2nd - testblog2");

    const entry_h2_title = await c.page.$eval(
      "h2.entry_header",
      (elm) => elm.textContent
    );
    const entry_body = await c.page.$eval(
      "div.entry_body",
      (elm) => elm.textContent
    );

    expect(entry_body.match(/2nd/)).toBeTruthy();
    expect(entry_h2_title.match(/2nd/)).toBeTruthy();
  });

  it("click prev page", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.click("a.preventry"),
    ]);

    await c.getSS("entry_prev_page");
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url + "?no=3");
    expect(await c.page.title()).toEqual("3rd - testblog2");

    const entry_h2_title = await c.page.$eval(
      "h2.entry_header",
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
  let post_comment_title;

  it("fill comment form", async () => {
    // check comment form display
    expect(
      (await c.page.$eval("#cm h3.sub_header", (elm) => elm.textContent)).match(
        /コメント/
      )
    ).toBeTruthy();
    expect(
      (
        await c.page.$eval(
          "#cm div.form h4.sub_title",
          (elm) => elm.textContent
        )
      ).match(/コメントの投稿/)
    ).toBeTruthy();

    // generate uniq title
    post_comment_title = "テストタイトル_" + Math.floor(Math.random() * 1000000).toString();

    await c.getSS("comment_before_fill");
    await fillCommentForm(
      c.page,
      "テスト太郎",
      post_comment_title,
      "test@example.jp",
      "http://example.jp",
      "これはテスト投稿です\nこれはテスト投稿です！",
      "pass_is_pass"
    )
    await c.getSS("comment_after_fill");

    const [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#comment_form input[type=submit]"),
    ]);

    await c.getSS("comment_confirm");
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url);
  });

  it("fail with wrong captcha", async () => {
    // input wrong captcha
    await c.page.type("input[name=token]", "0000"/*wrong key*/);

    const [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#sys-comment-form input[type=submit]"),
    ]);

    await c.getSS("comment_wrong_captcha");
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url);

    // 特定しやすいセレクタがないので、回してチェックする
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

  it("successful comment posting", async () => {
    await c.page.type("input[name=token]", captcha_key);
    await c.getSS("before_comment_success");

    const [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#sys-comment-form input[type=submit]"),
    ]);

    expect(response.status()).toEqual(200);
    const exp = new RegExp(
      start_url + "index.php\\?mode=entries&process=view&id=[0-9]{1,100}"
    );
    expect(response.url().match(exp)).not.toBeNull();

    await c.getSS("comment_success");

    const comment_a_text = await c.page.$eval("#e3 > div.entry_footer > ul > li:nth-child(2) > a[title=コメントの投稿]", elm => elm.textContent);
    posted_comment_num = parseInt(comment_a_text.match(/CM:([0-9]{1,3})/)[1]);
  });

  it("edit exists comment", async () => {
    await c.getSS("comment_edit_before");

    let [response1] = await Promise.all([
      c.waitLoad(),
      await (await getEditLinkByTitle(post_comment_title)).click(),
    ]);

    expect(response1.status()).toEqual(200);

    await fillEditForm(
      c.page,
      "テスト太郎2",
      "edited_" + post_comment_title,
      "test2@example.jp",
      "http://example.jp/2",
      "これは編集済み",
      "pass_is_pass"
    )

    await c.getSS("comment_edit_page");

    // 保存する
    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#comment_form > p > input[type=submit]:nth-child(1)"),
    ]);

    await c.getSS("comment_edit_confirm");
    expect(response.status()).toEqual(200);

    await c.page.type("input[name=token]", captcha_key);

    [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#sys-comment-form input[type=submit]"),
    ]);

    await c.getSS("comment_edit_success");
    expect(response.status()).toEqual(200);
  });

  it("fail comment delete by wrong password", async () => {
    await c.getSS("comment_delete_fail_before");

    // open comment edit page
    let edit_link = await getEditLinkByTitle("edited_" + post_comment_title);
    let [response1] = await Promise.all([
      c.waitLoad(),
      await edit_link.click(),
    ]);
    expect(response1.status()).toEqual(200);
    const h3_test = await c.page.$eval("#edit > h3.sub_header", (elm) => {
      return elm.textContent;
    });
    expect(h3_test.match(/コメントの編集/)).toBeTruthy();

    // click delete button
    const delete_button = await c.page.$(
      "#comment_form > p > input[type=submit]:nth-child(2)"
    );
    let [response] = await Promise.all([c.waitLoad(), await delete_button.click()]);
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url);

    // check shown error text
    const wrong_password_error_text = await c.page.$eval("#comment_form > dl > dd:nth-child(13) > p", elm => elm.textContent);
    expect(/必ず入力してください/.exec(wrong_password_error_text)).toBeTruthy();
  });

  it("successfully delete comment", async () => {
    await c.page.type("#pass", "pass_is_pass");
    await c.getSS("comment_before_delete");

    const [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#comment_form > p > input[type=submit]:nth-child(2)")
    ]);

    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url + "index.php?mode=entries&process=index");

    await c.getSS("comment_deleted");
    const comment_a_text = await c.page.$eval("#e3 > ul.entry_state > li > a[title=コメントの投稿]", elm => elm.textContent);
    const comment_num = parseInt(comment_a_text.match(/CM:([0-9]{1,3})/)[1]);
    expect(comment_num).toEqual(posted_comment_num/*-1*/); // TODO #224
  });

  afterAll(async () => {
    await c.browser.close();
  });

  // ========================

  async function getEditLinkByTitle(title): Promise<ElementHandle> {
    // 該当するタイトルの編集リンクを探す
    const elm_list = await c.page.$$("#cm div.sub_content");
    const data_list = [];
    for (let i = 0; i < elm_list.length; i++) {
      const item = {
        title: await (await elm_list[i].$eval("h4", elm => elm.textContent)),
        editLink: await (await elm_list[i].$("a[title='コメントの編集']")),
        debugHtml: await (await elm_list[i].getProperty('innerHTML')).jsonValue()
      };
      data_list.push(item);
    }

    let edit_link: ElementHandle;
    data_list.forEach((row) => {
      if (row.title === title) {
        edit_link = row.editLink;
      }
    });
    if(!edit_link){
      throw new Error("link(a[title='コメントの編集']) not found");
    }

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
      "#comment_form input[name='comment[name]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "#comment_form input[name='comment[name]']",
      name
    );
    await page.$eval(
      "#comment_form input[name='comment[title]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "#comment_form input[name='comment[title]']",
      title
    );
    await page.$eval(
      "#comment_form input[name='comment[mail]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "#comment_form input[name='comment[mail]']",
      email
    );
    await page.$eval(
      "#comment_form input[name='comment[url]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "#comment_form input[name='comment[url]']",
      url
    );
    await page.$eval(
      "#comment_form textarea[name='comment[body]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "#comment_form textarea[name='comment[body]']",
      body
    );
    await page.$eval(
      "#comment_form input[name='comment[pass]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "#comment_form input[name='comment[pass]']",
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
      "#comment_form input[name='edit[name]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "#comment_form input[name='edit[name]']",
      name
    );
    await page.$eval(
      "#comment_form input[name='edit[title]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "#comment_form input[name='edit[title]']",
      title
    );
    await page.$eval(
      "#comment_form input[name='edit[mail]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "#comment_form input[name='edit[mail]']",
      email
    );
    await page.$eval(
      "#comment_form input[name='edit[url]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "#comment_form input[name='edit[url]']",
      url
    );
    await page.$eval(
      "#comment_form textarea[name='edit[body]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "#comment_form textarea[name='edit[body]']",
      body
    );
    await page.$eval(
      "#comment_form input[name='edit[pass]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await page.type(
      "#comment_form input[name='edit[pass]']",
      pass
    );
  }
});
