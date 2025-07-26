<?php
namespace thursdaybw\DttMultiDeviceTestBase\Base;

class DesktopTestBase extends DeviceProfileTestBase {
  protected function getDeviceProfileKey(): string {
    return 'desktop';
  }
}

