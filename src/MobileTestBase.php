<?php
namespace thursdaybw\DttMultiDeviceTestBase\Base;

class MobileTestBase extends DeviceProfileTestBase {
  protected function getDeviceProfileKey(): string {
    return 'small_mobile';
  }
}
