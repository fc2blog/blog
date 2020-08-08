import { afterAll, beforeAll, describe, expect, it } from "@jest/globals";
import { Helper } from "./helper";

describe("will be redirect to random blog page", () => {
  let c: Helper;

  beforeAll(async () => {
    c = new Helper();
    await c.init();
  });

  it("redirect to random blog page", async () => {
    const response = await c.page.goto(c.getBaseUrl());
    const redirect = response.request().redirectChain()[0];
    expect(redirect.url()).toEqual(c.getBaseUrl());
    expect(redirect.response().headers().location).toMatch(/\/testblog[0-9]\//);
    expect(response.status()).toEqual(200);
  });

  it("get Title after redirected page.", async () => {
    expect(await c.getTitle()).toMatch(/testblog/);
  });

  afterAll(async () => {
    await c.browser.close();
  });
});
