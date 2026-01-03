<?php

namespace App\Feature\Media\Service;

use App\Entity\Media\FileObject;
use App\Feature\Media\DTO\FileUploadRequest;

/**
 * Interface for file management operations.
 * Allows for easy testing and alternative implementations.
 */
interface FileManagerInterface
{
    /**
     * Upload a file and create a FileObject entity.
     *
     * @param FileUploadRequest $request The upload request with file data
     * @param string $driverName The storage driver to use (e.g., 'local', 's3')
     * @return FileObject The created file object entity
     */
    public function upload(FileUploadRequest $request, string $driverName = 'local'): FileObject;

    /**
     * Get the public URL for a file.
     *
     * @param FileObject $file The file object
     * @return string The public URL
     */
    public function getPublicUrl(FileObject $file): string;

    /**
     * Delete a file (soft or hard delete).
     *
     * @param FileObject $file The file to delete
     * @param bool $hardDelete If true, permanently delete the file
     */
    public function delete(FileObject $file, bool $hardDelete = false): void;

    /**
     * Find a duplicate file by hash.
     *
     * @param string $hash The file hash
     * @return FileObject|null The duplicate file if found
     */
    public function findDuplicate(string $hash): ?FileObject;

    /**
     * Read the contents of a file.
     *
     * @param FileObject $file The file to read
     * @return string The file contents
     */
    public function read(FileObject $file): string;
}
