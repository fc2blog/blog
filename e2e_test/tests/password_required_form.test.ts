import { afterAll, beforeAll, describe, expect, it } from "@jest/globals";
import { Helper } from "./helper";

describe("crawl password required blog", () => {
  let c: Helper;

  beforeAll(async () => {
    c = new Helper();
    await c.init();
  });

  const start_url = "http://localhost:8080/testblog3/";

  it("open password required page", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.goto(start_url),
    ]);

    await c.getSS("password_required_page");

    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual("http://localhost:8080/testblog3/index.php?mode=entries&process=blog_password");
    expect(await c.page.title()).toEqual("testblog3");

    const body_text = await c.page.$eval("body", elm=>elm.textContent);
    expect(body_text.match(/パスワード認証/));
  });

  it("access with wrong password", async () => {
    await c.page.type(
        "input[name='blog[password]']",
        "wrongpass"
    );

    await c.getSS("wrong_password_typed");

    // 保存する
    const [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("input[type=submit]"),
    ]);

    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual("http://localhost:8080/testblog3/index.php?mode=entries&process=blog_password");
    expect(await c.page.title()).toEqual("testblog3");

    const body_text = await c.page.$eval("body", elm=>elm.textContent);
    expect(body_text.match(/パスワードが違います/));
  });

  it("access with password", async () => {
    await c.page.type(
        "input[name='blog[password]']",
        "password"
    );

    await c.getSS("password_typed");

    // 保存する
    const [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("input[type=submit]"),
    ]);

    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual("http://localhost:8080/testblog3/index.php?mode=entries&process=index");
    expect(await c.page.title()).toEqual("testblog3");

    const body_text = await c.page.$eval("body", elm=>elm.textContent);
    expect(body_text.match(/パスワードが必要なブログです。/));
  });

  afterAll(async () => {
    await c.browser.close();
  });
});
