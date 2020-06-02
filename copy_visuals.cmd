@echo off

SET SCRIPT_PATH=%~dp0
SET VISUAL_BACKUP=%SCRIPT_PATH%_test\data
SET PLUGIN_NAME=minimap

echo .
echo Backup of the dokuwiki pages that serves as visual into the directory %VISUAL_BACKUP%
echo .
echo .
SET DOKU_ROOT=%SCRIPT_PATH%..\..\..
SET DOKU_DATA=%DOKU_ROOT%\dokudata

SET VISUAL_PAGES=%DOKU_DATA%\pages\%PLUGIN_NAME%
SET VISUAL_PAGES_DST=%VISUAL_BACKUP%\pages

echo Copying the pages:
echo   * from %VISUAL_PAGES%
echo   * to %VISUAL_PAGES_DST%
echo .
call copy %VISUAL_PAGES% %VISUAL_PAGES_DST%
echo .

SET VISUAL_MEDIAS=%DOKU_DATA%\media\%PLUGIN_NAME%
SET VISUAL_MEDIAS_DST=%VISUAL_BACKUP%\media

echo Copying the images:
echo   * from %VISUAL_MEDIAS%
echo   * to %VISUAL_MEDIAS_DST%
echo .
call copy /V /Y %VISUAL_MEDIAS% %VISUAL_MEDIAS_DST%
echo .
echo Done