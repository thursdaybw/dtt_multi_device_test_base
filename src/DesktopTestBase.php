<?php
namespace thursdaybw\DttMultiDeviceTestBase;

class DesktopTestBase extends DeviceProfileTestBase {
  protected function getDeviceProfileKey(): string {
    return 'desktop';
  }
}

