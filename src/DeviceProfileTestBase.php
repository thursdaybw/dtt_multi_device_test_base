<?php
namespace thursdaybw\DttMultiDeviceTestBase;

use Drupal\Component\Serialization\Yaml;
use weitzman\DrupalTestTraits\ExistingSiteSelenium2DriverTestBase;
use Behat\Mink\Session;
use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Driver\Selenium2Driver;
use Drupal\FunctionalJavascriptTests\JSWebAssert;
use Composer\InstalledVersions;

/**
 * Base class that reads its Selenium args from tests/device_profiles.yaml.
 */
abstract class DeviceProfileTestBase extends ExistingSiteSelenium2DriverTestBase {

  /** @var \Behat\Mink\Session */
  protected $session;

  /**
   * Set up the test environment using a custom device profile.
   *
   * This method overrides the base setUp to inject a device-specific WebDriver
   * configuration into the DTT (Drupal Test Traits) stack before the session is created.
   *
   * It:
   * - Retrieves the desired device profile key (e.g., 'small_mobile') from the test class.
   * - Resolves the full device profile configuration from the YAML file path, which may
   *   come from the environment or default to the project's test resources.
   * - Sets the DTT_MINK_DRIVER_ARGS environment variable dynamically, allowing DTT's
   *   internal `getDriverInstance()` logic to use the correct Chrome emulation options.
   * - Finally, calls the parent setUp, which initializes the Mink session using the
   *   injected driver arguments, ensuring that all DTT features like `assertSession()`,
   *   `createUser()`, and `drupalLogin()` work correctly without further hacks.
   *
   * The order is critical:
   * - putenv() must be called *before* parent::setUp() to ensure DTT builds the driver
   *   using the desired profile.
   * - $this->driver is then assigned manually using getDriverInstance() so typed access
   *   to $this->driver in the base class doesn't cause a fatal error.
   */
  protected function setUp(): void {
      // Retrieve the device profile key from the test class (e.g. 'small_mobile').
      $profile = $this->getDeviceProfileKey();
      $yamlPath = $this->getDeviceProfilesPath(); // ← reuse the same logic

      // Parse the YAML and extract the requested profile config.
      $raw = file_get_contents($yamlPath);
      $profiles = \Symfony\Component\Yaml\Yaml::parse($raw);

      if (empty($profiles[$profile])) {
          throw new \RuntimeException("No profile '$profile' in $yamlPath");
      }

      // Set the env var DTT expects before it builds the driver.
      putenv('DTT_MINK_DRIVER_ARGS=' . json_encode($profiles[$profile]));

      // Let DTT initialize the Mink session using the driver created from our config.
      parent::setUp();

      // Ensure $this->driver (typed property in ExistingSiteBase) is initialized.
      $this->driver = $this->getDriverInstance();
  }

  protected function getDriverInstance(): DriverInterface {
    $profile = $this->getDeviceProfileKey();
    $path = $this->getDeviceProfilesPath();

    $raw = file_get_contents($path);
    $profiles = Yaml::decode($raw);

    if (empty($profiles[$profile]) || !is_array($profiles[$profile])) {
      throw new \RuntimeException("No device profile '$profile' in $path");
    }

    return new Selenium2Driver(...$profiles[$profile]);
  }

  protected function getDeviceProfilesPath(): string {
    // Get the env var value.
    $path = getenv('DTT_DEVICE_PROFILE_YAML') ?: ($_ENV['DTT_DEVICE_PROFILE_YAML'] ?? null);

    if ($path && !str_starts_with($path, '/')) {
      // ⚠️ This attempts to resolve the path relative to the directory PHPUnit was *invoked* from.
      // In theory, getcwd() should be that directory — typically the project root.
      // However, in practice, some setups (e.g. DTT, vendor/bin/phpunit, test runner shims, or IDEs)
      // mysteriously shift getcwd() to the web root (e.g. /var/www/html/web) or elsewhere.
      // So we prepend ".." to try and land back in the actual project root.
      // It's a compromise: avoids requiring a full path, but might still break in exotic setups.
      $path = getcwd() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $path;
    }

    // If still not valid, fallback to bundled default file (if you want that behavior).
    if (!$path || !file_exists($path)) {
      $fallback = __DIR__ . '/../../device_profiles.default.yaml';
      if (file_exists($fallback)) {
        return $fallback;
      }
      throw new \RuntimeException("Device profiles YAML not found. Looked for: $path");
    }

    return $path;
  }

  /**
   * Subclasses must return their profile key e.g. 'desktop' or 'small_mobile'.
   *
   * @return string
   *   A key from device_profiles.yaml.
   */
  abstract protected function getDeviceProfileKey(): string;

}

