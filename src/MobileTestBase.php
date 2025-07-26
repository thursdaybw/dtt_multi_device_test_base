<?php
namespace thursdaybw\DttMultiDeviceTestBase;

class MobileTestBase extends DeviceProfileTestBase {
  protected function getDeviceProfileKey(): string {
    return 'small_mobile';
  }
}
