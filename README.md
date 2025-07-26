# 🧪 DTT Multi Device Test Base

Reusable **base classes** for running [**Drupal Test Traits (DTT)**](https://github.com/weitzman/drupal-test-traits) tests in **multiple browser/device contexts** — such as desktop, mobile, tablet, or anything Selenium supports.

This package lets each PHPUnit test class declare its own use of a **device profile** defined in a shared yaml file.

It enables enabling true per-test responsive assertions like:


```php
class HomepageMobileTest extends MobileTestBase {}
class HomepageDesktopTest extends DesktopTestBase {}
```

✅ Works even in a single test run — no need for multiple configs or CI matrix gymnastics.

It fills a gap that cannot be solved by multiple PHPUnit configs or CI matrix runs alone.

This gives you:

    Multiple device profiles in one test suite

    Semantically clear test classes like GenerateCaptionsMobileTest vs GenerateCaptionsDesktopTest

    Flexibility beyond what DTT’s env-var-based driver loading allows

---

## Features

- `DeviceProfileTestBase` class:
  - Loads [Mink](https://mink.behat.org/) Selenium2Driver args from a YAML file
  - Allows subclasses to declare it's use of a a device profile like `'small_mobile'` or `'desktop'` via `getDeviceProfileKey()`
- Base class (`DeviceProfileTestBase`) that:
  - Loads custom Selenium2Driver capabilities from a YAML config file
- Includes `DesktopTestBase` and `MobileTestBase` ready to use
- Built on top of `ExistingSiteSelenium2DriverTestBase` from DTT
- Compatible with Composer-based Drupal installs
- Avoids test logic pollution like `if (mobile)` inside a shared test
- Supports fallback default profiles

---

## 💡 Why Not Just Use PHPUnit Configs?

Multiple configs or CI matrixes **run the same tests in different environments** — but they **can’t change behavior per test class**.

This library allows distinct classes like:

* `GenerateCaptionsMobileTest` to assert mobile-only behavior
* `GenerateCaptionsDesktopTest` to assert desktop-only UI

That's fine for:

- "Run all tests in Chrome"
- "Run the same test in Firefox, too"

But it **doesn't allow** writing a test like:

```php
class HomepageDesktopTest extends DesktopTestBase
class HomepageMobileTest extends MobileTestBase
```

These aren't alternate environments. They're **different tests** with different expectations.

Consider this use case:

    GenerateCaptionsMobileTest asserts mobile behavior

    GenerateCaptionsDesktopTest asserts desktop behavior

They are different tests with different expectations. Running the same class in different configs won't help, you'd either:

    * Skip or fail assertions that don’t apply to the current env

    * Or make the test logic messier with branching like if (MOBILE) {...}

## Installation

### Require the Library

Until it's released on Packagist, use the VCS method.

in composer.json:
```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/thursdaybw/dtt_multi_device_test_base"
    }
  ],
}
```

Then:

```bash
composer require --dev thursdaybw/dtt_multi_device_test_base:dev-main
```

---
## 🧩 Dependencies

This module assumes you're already using [Drupal Test Traits (DTT)](https://github.com/weitzman/drupal-test-traits) and have it fully configured.

---

## ⚙️ Configuration

### 1. Ensure You're Using DTT's `bootstrap.php`

You **must use DTT’s full bootstrap**, *not* `bootstrap-fast.php`, in your `phpunit.dtt.xml`:

```xml
<phpunit bootstrap="vendor/weitzman/drupal-test-traits/src/bootstrap.php">
```

🚫 `bootstrap-fast.php` is faster but skips full autoloading, which will break test class discovery for this module.

⚠️ bootstrap-fast.php may work but autoloading with boostrap-fast.php is untested.
---

The default file (`device_profiles.default.yaml`) is included, but you can create your own like this:

### 2. (optional) Configure the Device Profile YAML Path

In your `phpunit.xml` or `phpunit.dtt.xml`:

```xml
<php>
  <env name="DTT_DEVICE_PROFILE_YAML" value="/full/path/to/dtt_device_profiles.yaml"/>
</php>
```

✅ **Use a full path.**
Relative paths to the project root do work but are flaky and PHPUnit may change the working directory under the hood, especially in IDEs or CI.

---

### 2. (optional) Create Your YAML Config

Example `dtt_device_profiles.yaml`:

```yaml
small_mobile:
  - "chrome"
  - chromeOptions:
      mobileEmulation: { deviceName: "Pixel 2" }
      args: ["--window-size=412,732", "--disable-gpu", "--no-sandbox"]
  - "http://selenium-chrome:4444/wd/hub"

desktop:
  - "chrome"
  - chromeOptions:
      args: ["--window-size=1920,1080", "--disable-gpu", "--no-sandbox"]
  - "http://selenium-chrome:4444/wd/hub"
```

---

## Usage


Extend the provided base classes that use the default config:

```php
use thursdaybw\DttMultiDeviceTestBase\MobileTestBase;

class HomepageMobileTest extends MobileTestBase {
  public function testMobileNav() {
    $this->visit('/');
    $this->assertSession()->elementNotExists('css', '.desktop-nav');
  }
}
```

```phpy
use thursdaybw\DttMultiDeviceTestBase\DesktopTestBase;

class HomepageDesktopTest extends DesktopTestBase {
  public function testDesktopNav() {
    $this->visit('/');
    $this->assertSession()->elementExists('css', '.desktop-nav');
  }
}
```

or Extend DeviceProfileTestBase in your test classes to use your own config:


```php
use thursdaybw\DttMultiDeviceTestBase\DeviceProfieTestBase;

class MyMobileTest extends DeviceProfileTestBase {
  protected function getDeviceProfileKey(): string {
    return 'small_mobile'; # or whatever name you defined in your own yaml config
  }

  public function testMobileStuff() {
    $this->visit('/');
    $this->assertSession()->elementNotExists('css', '.desktop-nav');
  }
}
```


