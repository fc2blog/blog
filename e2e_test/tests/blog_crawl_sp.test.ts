import { afterAll, beforeAll, describe, expect, it } from "@jest/globals";
import { Helper } from "./helper";

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

  it("fill comment", async () => {
    await c.page.type(
      "form[name=form1] input[name='comment[name]']",
      "テスト太郎"
    );
    await c.page.type(
      "form[name=form1] input[name='comment[title]']",
      "テストタイトル"
    );
    await c.page.type(
      "form[name=form1] input[name='comment[mail]']",
      "test@example.jp"
    );
    await c.page.type(
      "form[name=form1] input[name='comment[url]']",
      "http://example.jp"
    );
    await c.page.type(
      "form[name=form1] textarea[name='comment[body]']",
      "これはテスト投稿です\nこれはテスト投稿です！"
    );
    await c.page.type(
      "form[name=form1] input[name='comment[pass]']",
      "pass_is_pass"
    );

    await c.getSS("comment_filled_sp.png");

    const [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#comment_post > form > div > a"),
    ]);

    await c.getSS("comment_confirm_sp.png");

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

    await c.getSS("comment_wrong_captcha_sp.png");

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
    await c.getSS("comment_correct_token_sp.png");

    const [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#sys-comment-form input[type=submit]"),
    ]);

    expect(response.status()).toEqual(200);
    const exp = new RegExp(
      start_url + 'index.php\\?mode=entries&process=view&id=[0-9]{1,100}'
    );

    expect(response.url().match(exp)).not.toBeNull();

    const comment_a_text = await c.page.$eval("#entry > ul > li:nth-child(2) > a", elm=>elm.textContent);

    await c.getSS("comment_success_sp.png");
    posted_comment_num = parseInt(comment_a_text.replace(/^コメント\(/,'').replace(/\)$/,''));
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
    const link = await c.page.$("#comment > dl > dd:nth-child(2) > p > a:nth-child(4)");
    const [response] = await Promise.all([
      c.waitLoad(),
      link.click()
    ]);
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url + "index.php?mode=entries&process=comment_edit&id=1");
    expect(await c.page.title()).toEqual("- testblog2"); // TODO issue #223
    await c.getSS("comment_edit_before_sp");
  });

  it("comment edit", async () => {
    await c.page.$eval(
      "#comment_post input[name='edit[name]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await c.page.type("#comment_post input[name='edit[name]']", "テスト太郎2");
    await c.page.$eval(
      "#comment_post input[name='edit[title]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await c.page.type(
      "#comment_post input[name='edit[title]']",
      "テストタイトル2"
    );
    await c.page.$eval(
      "#comment_post input[name='edit[mail]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await c.page.type(
      "#comment_post input[name='edit[mail]']",
      "test@example.jp2"
    );
    await c.page.$eval(
      "#comment_post input[name='edit[url]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await c.page.type(
      "#comment_post input[name='edit[url]']",
      "http://example.jp/2"
    );
    await c.page.$eval(
      "#comment_post textarea[name='edit[body]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await c.page.type(
      "#comment_post textarea[name='edit[body]']",
      "これは編集済み"
    );
    await c.page.$eval(
      "#comment_post input[name='edit[pass]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await c.page.type("#comment_post input[name='edit[pass]']", "pass_is_pass");

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

  it("open comment list to delete", async () => {
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
    const link = await c.page.$("#comment > dl > dd:nth-child(2) > p > a:nth-child(4)");
    const [response] = await Promise.all([
      c.waitLoad(),
      link.click()
    ]);
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url + "index.php?mode=entries&process=comment_edit&id=1");
    expect(await c.page.title()).toEqual("- testblog2"); // TODO issue #223
    await c.getSS("comment_form_delete_before_sp");
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

  it("comment delete success", async () => {
    await c.page.type("#comment_post > form > dl > dd:nth-child(12) > input[type=password]", "pass_is_pass");

    const [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#comment_post > form > div > input[type=submit]:nth-child(2)")
    ]);

    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url+"index.php?mode=entries&process=index&sp");
  });

  it("open entry check delete complete", async () => {
    const link = await c.page.$("#entry_list > li:nth-child(1) > a");
    const [response] = await Promise.all([
      c.waitLoad(),
      link.click()
    ]);
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url + "?no=3&sp");
    expect(await c.page.title()).toEqual("3rd - testblog2");
  });

  it("check comment count", async () => {
    const comment_a_text = await c.page.$eval("#entry > ul > li:nth-child(2) > a", elm=>elm.textContent);
    const comment_num = parseInt(comment_a_text.replace(/^コメント\(/,'').replace(/\)$/,''));
    expect(comment_num).toEqual(posted_comment_num);
  });

  afterAll(async () => {
    await c.browser.close();
  });
});
