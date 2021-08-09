<?php
declare(strict_types=1);

namespace Fc2blog\Service;

use Exception;
use Fc2blog\App;
use Fc2blog\Web\Request;
use MaxMind\Db\Reader;

class AccessBlock
{
    const MMDB_FILE_PATH = App::TEMP_DIR . "/GeoLite2-Country.mmdb";

    private $user_block_country_iso_code_csv;
    private $admin_block_country_iso_code_csv;

    public function __construct(
        string $user_block_country_iso_code_csv = "",
        string $admin_block_country_iso_code_csv = ""
    )
    {
        if (strlen($user_block_country_iso_code_csv) > 0) {
            $this->user_block_country_iso_code_csv = $user_block_country_iso_code_csv;
        } elseif (defined("USER_BLOCK_COUNTRY_ISO_CODE_CSV")) {
            $this->user_block_country_iso_code_csv = USER_BLOCK_COUNTRY_ISO_CODE_CSV;
        } else {
            $this->user_block_country_iso_code_csv = "";
        }

        if (strlen($admin_block_country_iso_code_csv) > 0) {
            $this->admin_block_country_iso_code_csv = $admin_block_country_iso_code_csv;
        } elseif (defined("ADMIN_BLOCK_COUNTRY_ISO_CODE_CSV")) {
            $this->admin_block_country_iso_code_csv = ADMIN_BLOCK_COUNTRY_ISO_CODE_CSV;
        } else {
            $this->admin_block_country_iso_code_csv = "";
        }

    }

    public function isAdminBlockIp(Request $request): bool
    {
        if (strlen($this->admin_block_country_iso_code_csv) === 0) return false;
        /** @noinspection PhpUnhandledExceptionInspection */ // エラーなら、アプリは停止で良い
        return $this->isBlockIp($request, $this->admin_block_country_iso_code_csv);
    }

    public function isUserBlockIp(Request $request): bool
    {
        if (strlen($this->user_block_country_iso_code_csv) === 0) return false;
        /** @noinspection PhpUnhandledExceptionInspection */ // エラーなら、アプリは停止で良い
        return $this->isBlockIp($request, $this->user_block_country_iso_code_csv);
    }

    /**
     * Check IP address that have to blocked with Read MaxMind Geo ip database.
     * @param Request $request
     * @param string $block_country_iso_code_csv
     * @return bool
     * @throws Reader\InvalidDatabaseException
     * @throws Exception
     */
    public function isBlockIp(Request $request, string $block_country_iso_code_csv): bool
    {
        if (
            !file_exists(self::MMDB_FILE_PATH) ||
            !is_file(self::MMDB_FILE_PATH) ||
            !is_readable(self::MMDB_FILE_PATH)
        ) {
            // mmdb file notfound. Not to be checking. Done.
            return false;
        }

        $reader = new Reader(self::MMDB_FILE_PATH);
        $result = $reader->get($request->getClientIpAddress());
        $reader->close();
        if (
            !is_array($result) || // If undetermined, Result will be null.
            !isset($result['country']) ||
            !isset($result['country']['iso_code'])
        ) {
            // Could not detect country information. So allow access.
            return false;
        }

        $determined_country_iso_code = $result['country']['iso_code'];

        return $this->isContainCsv($determined_country_iso_code, $block_country_iso_code_csv);
    }

    private function isContainCsv(string $country_iso_code, string $block_country_iso_code_csv): bool
    {
        $list = explode(',', $block_country_iso_code_csv);
        return in_array($country_iso_code, $list);
    }
}
