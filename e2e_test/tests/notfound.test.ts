import { afterAll, beforeAll, describe, expect, it } from "@jest/globals";
import { Helper } from "./helper";

describe("crawl notfound page", () => {
  let c: Helper;

  const admin_id = "testadmin";
  const admin_pass = "testadmin";

  beforeAll(async () => {
    c = new Helper();
    await c.init();
  });

  it("open user not found page", async () => {
    const url = 'http://localhost:8080/testblog2/?no=99999999999';
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.goto(url),
    ]);

    await c.getSS("notfound");

    expect(response.status()).toEqual(404);
    expect(response.url()).toEqual(url);
    expect(await c.page.title()).toEqual("testblog2");

    const body_text = await c.page.$eval("body", elm=>elm.textContent);
    expect(body_text.match(/404 Not Found お探しのページは存在しません/));
  });

  it("login admin page", async () => {
    const start_url = "http://localhost:8080/admin/";
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.goto(start_url),
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    // ログインページにリダイレクトされる
    expect(response.url()).toEqual("http://localhost:8080/admin/users/login");

    await c.page.$eval(
        "#id_form input[name='user[login_id]']",
        (elm: HTMLInputElement) => (elm.value = "")
    );
    await c.page.type("#id_form input[name='user[login_id]']", admin_id);
    await c.page.$eval(
        "#id_form input[name='user[password]']",
        (elm: HTMLInputElement) => (elm.value = "")
    );
    await c.page.type("#id_form input[name='user[password]']", admin_pass);

    const [response2] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#id_form input[type=submit]"),
    ]);

    expect(response2.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response2.url()).toEqual("http://localhost:8080/admin/common/notice");

  });

  it("open admin not found page", async () => {
    const url = 'http://localhost:8080/admin/common/invalid_method';
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.goto(url),
    ]);

    await c.getSS("notfound_admin_page");

    expect(response.url()).toEqual(url);
    expect(response.status()).toEqual(404);
    expect(await c.page.title()).toEqual("404 Not Found. - testblog2");

    const body_text = await c.page.$eval("body", elm=>elm.textContent);
    expect(body_text.match(/404 Not Found お探しのページは存在しません/));
  });

  afterAll(async () => {
    await c.browser.close();
  });

  it("open not exists class in admin context, must be found page", async () => {
    const url = 'http://localhost:8080/admin/missing_class/invalid_method';
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.goto(url),
    ]);

    await c.getSS("notfound_admin_page");

    expect(response.url()).toEqual(url);
    expect(response.status()).toEqual(404);
    // expect(await c.page.title()).toEqual("404 Not Found."); // TODO: ユーザースペースのテンプレートのTwig化

    const body_text = await c.page.$eval("body", elm=>elm.textContent);
    expect(body_text.match(/404 Not Found お探しのページは存在しません/));
  });

  afterAll(async () => {
    await c.browser.close();
  });
});
