# E2E test by puppeteer

## Setup

```
$ npm ci

# If you want configure
$ cp .env.sample .env
$ vi .env
```

## Target fc2blog setup

- Start target fc2blog webapp.
- Run `phpunit` for pseudo data set loading.

> Recommend: use docker.
>
> If you run at own apache or other, Set `DEBUG_FORCE_CAPTCHA_KEY=1234` ENV.


## Execute e2e test

```
# All test execution.
$ npm run test

# or specify a test.
$ npx jest test/test.test.js
```

## Don't forget code format before test code commit.

```
# format by prettier
$ npm run fmt
```

