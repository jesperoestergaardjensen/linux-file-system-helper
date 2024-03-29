<?php

namespace LinuxFileSystemHelper\Tests\unit;

use LinuxFileSystemHelper\Exceptions\LinuxFileSystemHelperException;
use LinuxFileSystemHelper\FolderHelper;
use PHPUnit\Framework\TestCase;

class FolderHelperTest extends TestCase
{
    public static function getDataFolder(): string
    {
        return dirname(__DIR__) . "/data/";
    }

    public static function getTestFolder(): string
    {
        return self::getDataFolder() . "test-folder/";
    }

    public static function getPhotosTestFolder(): string
    {
        return self::getDataFolder() . "photos/";
    }

    public static function tearDownAfterClass(): void
    {
        if (file_exists(self::getTestFolder())) {
            rmdir(self::getTestFolder());
        }
    }

    public function testCreateFolderFailCase1()
    {
        $this->expectException(LinuxFileSystemHelperException::class);
        FolderHelper::createFolder('/invalid-path/');

    }

    public function testCreateFolderFailCase2()
    {
        $this->expectException(LinuxFileSystemHelperException::class);
        FolderHelper::createFolder('invalid-path');
    }

    public function testCreateFolderSuccess()
    {
        $folder_to_create = self::getTestFolder();
        FolderHelper::createFolder($folder_to_create);
        $this->assertTrue(is_dir($folder_to_create));
    }

    public function testListFilesRecursiveFromFolderCaseIgnoreFilter()
    {
        // Two file are expected with no filter
        $file_list = FolderHelper::listFilesRecursiveFromFolder(self::getPhotosTestFolder(), '.jpg');
        $this->assertCount(2, $file_list, 'Two file are expected with no filter');

        // One file is expected with filter
        $file_list = FolderHelper::listFilesRecursiveFromFolder(self::getPhotosTestFolder(), 'jpg', ['.trash']);
        $this->assertCount(1, $file_list, 'One file is expected with filter');
    }

    public function testListFilesRecursiveFromFolderCaseB()
    {
        // Make sure test work both locally and on GitHub actions
        touch(self::getPhotosTestFolder() . 'SampleJPGImage_100kbmb.jpg');

        $file_list = FolderHelper::listFilesRecursiveFromFolder(self::getPhotosTestFolder(), 'jpg', ['.trash']);

        $file_info_array = explode(';', $file_list[0]);

        $this->assertTrue(is_numeric($file_info_array[0]));
        $this->assertEquals(date('Y-m-d', time()), $file_info_array[1]);
        $this->assertEquals(self::getPhotosTestFolder() . 'SampleJPGImage_100kbmb.jpg', $file_info_array[2]);
    }

    public function testListFilesRecursiveFromFolderCaseC()
    {
        $file_list = FolderHelper::listFilesRecursiveFromFolder(self::getPhotosTestFolder(), 'pdf');
        $this->assertEmpty($file_list);
    }

    public function testIsFolderEmpty()
    {
        $path_and_file = self::getTestFolder() . 'random-file.txt';
        touch($path_and_file);

        $folder_to_create = self::getDataFolder();
        FolderHelper::createFolder($folder_to_create . 'empty-folder');

        $this->assertTrue(FolderHelper::isFolderEmpty(self::getDataFolder() . 'empty-folder'),
            'folder should be seen as empty');
        $this->assertFalse(FolderHelper::isFolderEmpty(self::getDataFolder() . 'non-empty-folder'),
            'folder should be seen as non-empty');

        $this->expectException(LinuxFileSystemHelperException::class);
        $this->expectExceptionMessage("The supplied path ($path_and_file) do not seem to be a folder!");
        try {
            FolderHelper::isFolderEmpty($path_and_file);
        } finally {
            unlink($path_and_file);
        }
    }
}
