import {afterAll, beforeAll, describe, expect, it} from "@jest/globals";
import {Helper} from "./helper";

describe("crawl admin pages", () => {
  let c: Helper;

  beforeAll(async () => {
    c = new Helper();
    await c.init();
  });

  const start_url = "/admin/common/install";

  it("open login page", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.goto(c.getBaseUrl() + start_url),
    ]);

    await c.getSS("admin_install.png");

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
  });

  // 実際のインストール処理が正常に行えるか？は追って追記
  // 当座開いてエラーがでないかだけをテスト

  afterAll(async () => {
    await c.browser.close();
  });
});
