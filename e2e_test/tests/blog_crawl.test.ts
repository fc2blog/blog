import { afterAll, beforeAll, describe, expect, it } from "@jest/globals";
import { Helper } from "./helper";

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
  let captcha_key: string;

  beforeAll(async () => {
    c = new Helper();
    await c.init();
    captcha_key = "1234";
  });

  const start_url = "http://localhost:8080/testblog2/";

  it("open blog top", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.goto(start_url),
    ]);

    await c.getSS("blog_top.png");

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
    // console.log(entry_bodies);

    expect(entry_bodies[0].match(/3rd/)).toBeTruthy();
    expect(entry_bodies[1].match(/2nd/)).toBeTruthy();
    expect(entry_bodies[2].match(/1st/)).toBeTruthy();
  });

  it("click  first entry's 「続きを読む」", async () => {
    const link = await c.page.$("div.entry_body a");

    const [response] = await Promise.all([c.waitLoad(), link.click()]);

    await c.getSS("entry.png");

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

  it("next page", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.click("a.nextentry"),
    ]);

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

  it("prev page", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.click("a.preventry"),
    ]);

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

  it("comment", async () => {
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

    await c.page.type(
      "#comment_form input[name='comment[name]']",
      "テスト太郎"
    );
    await c.page.type(
      "#comment_form input[name='comment[title]']",
      "テストタイトル"
    );
    await c.page.type(
      "#comment_form input[name='comment[mail]']",
      "test@example.jp"
    );
    await c.page.type(
      "#comment_form input[name='comment[url]']",
      "http://example.jp"
    );
    await c.page.type(
      "#comment_form textarea[name='comment[body]']",
      "これはテスト投稿です\nこれはテスト投稿です！"
    );
    await c.page.type(
      "#comment_form input[name='comment[pass]']",
      "pass_is_pass"
    );

    await c.getSS("comment_filled.png");

    const [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#comment_form input[type=submit]"),
    ]);

    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url);

    await c.getSS("comment_confirm.png");
  });

  it("failed with wrong captcha", async () => {
    // input wrong captcha
    await c.page.type("input[name=token]", "0000"); // wrong key

    const [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#sys-comment-form input[type=submit]"),
    ]);

    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url);

    await c.getSS("comment_wrong_captcha.png");

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
    await c.getSS("comment_success.png");

    const [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#sys-comment-form input[type=submit]"),
    ]);

    await c.getSS("comment_success.png");

    expect(response.status()).toEqual(200);
    const exp = new RegExp(
      start_url + "index.php\\?mode=entries&process=view&id=[0-9]{1,100}"
    );
    expect(response.url().match(exp)).not.toBeNull();
  });

  it("comment edit", async () => {
    // NOTE: html構造的に、「編集」リンクが探しづらい
    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#cm #comment1 > ul > li:nth-child(3) > a"),
    ]);

    expect(response.status()).toEqual(200);

    await c.page.$eval(
      "#comment_form input[name='edit[name]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await c.page.type("#comment_form input[name='edit[name]']", "テスト太郎2");
    await c.page.$eval(
      "#comment_form input[name='edit[title]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await c.page.type(
      "#comment_form input[name='edit[title]']",
      "テストタイトル2"
    );
    await c.page.$eval(
      "#comment_form input[name='edit[mail]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await c.page.type(
      "#comment_form input[name='edit[mail]']",
      "test@example.jp2"
    );
    await c.page.$eval(
      "#comment_form input[name='edit[url]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await c.page.type(
      "#comment_form input[name='edit[url]']",
      "http://example.jp/2"
    );
    await c.page.$eval(
      "#comment_form textarea[name='edit[body]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await c.page.type(
      "#comment_form textarea[name='edit[body]']",
      "これは編集済み"
    );
    await c.page.$eval(
      "#comment_form input[name='edit[pass]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await c.page.type("#comment_form input[name='edit[pass]']", "pass_is_pass");

    await c.getSS("comment_edit_page.png");

    // 保存する
    [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#comment_form > p > input[type=submit]:nth-child(1)"),
    ]);

    await c.getSS("comment_edit_confirm.png");
    expect(response.status()).toEqual(200);

    await c.page.type("input[name=token]", captcha_key);

    [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#sys-comment-form input[type=submit]"),
    ]);

    await c.getSS("comment_edit_success.png");
    expect(response.status()).toEqual(200);
  });

  // WIP、削除機能がうごいていないので。
  it("comment delete", async () => {
    const comment1 = await c.page.$("#comment1");
    const title = await comment1.$eval(
      "h4.sub_title",
      (elm) => elm.textContent
    );
    const edit_a = await c.page.$("#comment1 > ul > li:nth-child(3) > a");

    // NOTE: html構造的に、「編集」リンクが探しづらい
    let [response] = await Promise.all([c.waitLoad(), await edit_a.click()]);

    expect(response.status()).toEqual(200);

    const h3_test = await c.page.$eval("#edit > h3.sub_header", (elm) => {
      return elm.textContent;
    });
    expect(h3_test.match(/コメントの編集/)).toBeTruthy();

    const delete_button = await c.page.$(
      "#comment_form > p > input[type=submit]:nth-child(2)"
    );

    [response] = await Promise.all([c.waitLoad(), await delete_button.click()]);

    // TODO 削除機能がうごいていないので完成と言えない
  });

  afterAll(async () => {
    await c.browser.close();
  });
});
