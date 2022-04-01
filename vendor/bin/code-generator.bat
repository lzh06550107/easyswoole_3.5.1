@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../easyswoole/code-generation/bin/code-generator
php "%BIN_TARGET%" %*
