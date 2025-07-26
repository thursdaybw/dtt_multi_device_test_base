# 🧪 DTT Multi Device Test Base

Reusable base classes for running **Drupal Test Traits (DTT)** tests in **multiple browser/device contexts** — such as desktop, mobile, tablet, or anything Selenium supports.

This module enables **per-test-class device profiles**, using custom base classes that read from a YAML config and spawn a Mink session accordingly.
It lets you assert responsive behavior directly within PHPUnit.

It fills a gap that cannot be solved by multiple PHPUnit configs or CI matrix runs alone.

This gives you:

    Multiple device profiles in one test suite

    Semantically clear test classes like GenerateCaptionsMobileTest vs GenerateCaptionsDesktopTest

    Flexibility beyond what DTT’s env-var-based driver loading allows

---

## Features

- Base class (`DeviceProfileTestBase`) that:
  - Loads custom Selenium2Driver capabilities from a YAML config file
  - Allows subclasses to declare a target device profile (`getDeviceProfileKey()`)
  - Works with DTT’s `ExistingSiteSelenium2DriverTestBase`
- Compatible with Composer-based Drupal installs
- Supports fallback default profiles

---

## 💡 Why This Exists

Drupal Test Traits (DTT) allows browser testing using Selenium, but all tests run in a single environment defined via env vars or `phpunit.xml`.

That’s fine for:

- “Run all tests in Chrome”
- “Run the same test in Firefox, too”

But it **doesn’t allow** writing a test like:

```php
class HomepageDesktopTest extends DesktopTestBase
class HomepageMobileTest extends MobileTestBase
```

### "Why not just use multiple PHPUnit configs or CI matrix?"

That approach can run the same test suite in different environments but it can't let a single test class declare its device context and behave differently.

Consider this use case:

    GenerateCaptionsMobileTest asserts mobile behavior

    GenerateCaptionsDesktopTest asserts desktop behavior

They are different tests with different expectations. Running the same class in different configs won't help, you'd either:

    * Skip or fail assertions that don’t apply to the current env

    * Or make the test logic messier with branching like if (MOBILE) {...}


## Installation

Install as a custom Drupal module (in `modules/custom` or `modules/contrib`):


Here's a clearer, more structured rewrite of the **Configuration** section for your README:

---

## ⚙️ Configuration

This module assumes you're already using [Drupal Test Traits (DTT)](https://github.com/weitzman/drupal-test-traits) and have it fully configured.

### 1. Set the Device Profile YAML Path

In your `phpunit.xml` (usually in your project root), define the path to your device profile YAML:

```xml
<php>
  <env name="DTT_DEVICE_PROFILE_YAML" value="/full/path/to/dtt_device_profiles.yaml"/>
</php>
```

✅ **Recommended:** Use a **full absolute path**
Relative paths are unreliable due to quirks in PHPUnit’s working directory (`getcwd()`) and may break depending on how tests are invoked (e.g. via IDE, shell, or CI).

---

### 2. Create Your Device Profile YAML File

Place your `dtt_device_profiles.yaml` in the project root (alongside `phpunit.xml` and `composer.json`). Example:

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

✅ A fallback file (`device_profiles.default.yaml`) is bundled in this repo, but it’s not maintained.
Copy and customize your own YAML config.

---

### 3. Ensure You're Using DTT's `bootstrap.php`

You **must use DTT’s full bootstrap**, *not* `bootstrap-fast.php`, in your `phpunit.dtt.xml`:

```xml
<phpunit bootstrap="vendor/weitzman/drupal-test-traits/src/bootstrap.php">
```

🚫 `bootstrap-fast.php` is faster but skips full autoloading, which will break test class discovery for this module.

⚠️ bootstrap-fast.php may work but autoloading with boostrap-fast.php is untested.
---

## 🧩 Dependencies

You **must onfigure [weitzman/drupal-test-traits](https://github.com/weitzman/drupal-test-traits)** for this module to function.

## Usage

Extend DeviceProfileTestBase in your test classes:

```php
use Drupal\Tests\dtt_multi_device_test_base\ExistingSiteJavascript\Base\DeviceProfileTestBase;

class MyMobileTest extends DeviceProfileTestBase {
  protected function getDeviceProfileKey(): string {
    return 'small_mobile';
  }

  public function testMobileStuff() {
    $this->visit('/');
    $this->assertSession()->elementNotExists('css', '.desktop-nav');
  }
}
```


