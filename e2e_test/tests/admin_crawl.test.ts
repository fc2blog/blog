import { afterAll, beforeAll, describe, expect, it } from "@jest/globals";
import { Helper } from "./helper";

describe("crawl admin pages", () => {
  let c: Helper;

  const admin_id = "testadmin";
  const admin_pass = "testadmin";

  beforeAll(async () => {
    c = new Helper();
    await c.init();
  });

  const start_url = "http://localhost:8080/admin/";

  it("open login page", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.goto(start_url),
    ]);

    await c.getSS("admin_login.png");

    // check session cookie.
    const cookies = await c.page.cookies()
    expect(cookies.length).toEqual(1);
    expect(cookies[0].name).toEqual('dojima');
    expect(cookies[0].domain).toEqual('localhost');
    expect(cookies[0].path).toEqual('/');
    expect(cookies[0].expires).toEqual(-1);
    expect(cookies[0].httpOnly).toEqual(true);
    expect(cookies[0].secure).toEqual(false);
    expect(cookies[0].session).toEqual(true);
    expect(cookies[0].sameSite).toEqual("Lax");

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    // ログインページにリダイレクトされる
    expect(response.url()).toEqual("http://localhost:8080/admin/users/login");
  });

  it("login failed", async () => {
    await c.page.type(
      "#id_form input[name='user[login_id]']",
      "wrong-login-id"
    );
    await c.page.type(
      "#id_form input[name='user[password]']",
      "wrong-login-pass"
    );

    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#id_form input[type=submit]"),
    ]);

    await c.getSS("admin_login_fail.png");

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual("http://localhost:8080/admin/users/login");

    const flash_message = await c.page.$eval(
      "#main-contents > div.flash-message-error > p",
      (elm) => elm.textContent
    );
    expect(/入力エラーがあります/.exec(flash_message)).not.toBeNull();

    const input_error_message = await c.page.$eval(
      "#id_form > tbody > tr:nth-child(1) > td",
      (elm) => elm.textContent
    );
    expect(/ログインIDまたはパスワードが違います/.exec(input_error_message)).not.toBeNull();
  });

  it("login success", async () => {
    await c.page.$eval(
        "#id_form input[name='user[login_id]']",
        (elm: HTMLInputElement) => (elm.value = "")
    );
    await c.page.type(
      "#id_form input[name='user[login_id]']",
      admin_id
    );
    await c.page.$eval(
        "#id_form input[name='user[password]']",
        (elm: HTMLInputElement) => (elm.value = "")
    );
    await c.page.type(
      "#id_form input[name='user[password]']",
      admin_pass
    );

    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#id_form input[type=submit]"),
    ]);

    await c.getSS("admin_login_success.png");

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual("http://localhost:8080/admin/common/notice");
  });

  it("open お知らせ", async () => {
    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#left-nav > div:nth-child(1) > ul > li:nth-child(1) > a[href='/admin/common/notice']"),
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual("http://localhost:8080/admin/common/notice");
  });

  it("open 新しく記事を書く", async () => {
    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#left-nav > div:nth-child(1) > ul > li:nth-child(3) > a[href='/admin/entries/create']"),
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual("http://localhost:8080/admin/entries/create");
  });

  it("open 記事一覧", async () => {
    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#left-nav > div:nth-child(1) > ul > li:nth-child(4) > a[href='/admin/entries/index']"),
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual("http://localhost:8080/admin/entries/index");
  });

  it("open コメント一覧", async () => {
    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#left-nav > div:nth-child(1) > ul > li:nth-child(5) > a[href='/admin/comments/index']"),
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual("http://localhost:8080/admin/comments/index");
  });

  it("open ファイルアップロード", async () => {
    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#left-nav > div:nth-child(1) > ul > li:nth-child(6) > a[href='/admin/files/upload']"),
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual("http://localhost:8080/admin/files/upload");
  });

  it("open テンプレート管理", async () => {
    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#left-nav > div:nth-child(2) > ul > li:nth-child(1) > a[href='/admin/blog_templates/index']"),
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual("http://localhost:8080/admin/blog_templates/index");
  });

  it("open プラグイン管理", async () => {
    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#left-nav > div:nth-child(2) > ul > li:nth-child(2) > a[href='/admin/blog_plugins/index']"),
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual("http://localhost:8080/admin/blog_plugins/index");
  });

  it("open カテゴリー管理", async () => {
    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#left-nav > div:nth-child(2) > ul > li:nth-child(3) > a[href='/admin/categories/create']"),
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual("http://localhost:8080/admin/categories/create");
  });

  it("open タグ一覧", async () => {
    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#left-nav > div:nth-child(2) > ul > li:nth-child(4) > a[href='/admin/tags/index']"),
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual("http://localhost:8080/admin/tags/index");
  });

  it("open ブログ一覧", async () => {
    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#left-nav > div:nth-child(3) > ul > li:nth-child(1) > a[href='/admin/blogs/index']"),
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual("http://localhost:8080/admin/blogs/index");
  });

  it("open ユーザー設定", async () => {
    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#left-nav > div:nth-child(3) > ul > li:nth-child(2) > a[href='/admin/users/edit']"),
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual("http://localhost:8080/admin/users/edit");
  });

  it("change language to en", async () => {
    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.select("#sys-language-setting", "en"),
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual("http://localhost:8080/admin/users/edit");

    const home_string = await c.page.$eval("#left-nav > div:nth-child(1) > h3", elm=>elm.textContent);
    expect(/Home/.exec(home_string)).not.toEqual(null);
  });

  it("change language to ja", async () => {
    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.select("#sys-language-setting", "ja"),
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual("http://localhost:8080/admin/users/edit");

    const home_string = await c.page.$eval("#left-nav > div:nth-child(1) > h3", elm=>elm.textContent);
    expect(/ホーム/.exec(home_string)).not.toEqual(null);
  });

  it("logout success", async () => {
    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#left-nav > div:nth-child(3) > ul > li:nth-child(3) > a[href='/admin/users/logout']"),
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual("http://localhost:8080/admin/users/login");

    const h2_text = await c.page.$eval(
        "#main-contents > header > h2",
        (elm) => elm.textContent
    );
    expect(/ログイン/.exec(h2_text)).not.toBeNull();
  });

  afterAll(async () => {
    await c.browser.close();
  });
});
