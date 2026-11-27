# Android APK releases

Deploy migration: php artisan migrate --force. The public page is /download/app and the stable file URL is /download/android. Admins upload a new APK under /admin/app-releases, then publish it. Publishing archives previous releases, which can be republished. Uploads are private and not in Git.

For uploads up to 150 MB, set PHP upload_max_filesize to at least 150M, post_max_size above that, and configure Livewire temporary_file_upload.rules to permit files up to 153600 KB (its default is often 12 MB). Adjust cPanel/proxy limits and timeouts as needed. PHP ZipArchive is required. The upload checks ZIP structure and APK contents, not cryptographic signatures or package identity; upload only a verified, licensed release. Download requests are not confirmed installs or completed transfers. Back up storage/app/private/app-releases and database; old APKs are intentionally retained. Test on staging before production.
