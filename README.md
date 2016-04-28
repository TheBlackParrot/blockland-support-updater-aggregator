## Configuration
`/includes/repos.txt` is a file continaing all repositories to list; 1 repository per line  
This file must exist in order for the updater to function.

## Updating
`/includes/updater.php` can be called from a Cron daemon or etc. to automatically update the cache file.  
Once the script is run once, `/includes/cache.json` will be generated.

## Cache file
`/includes/cache.json` contains:

- The repository status (could it be reached)
- Repository URL (also in each repository key)
- The last time the updater detected a change in the repository
- The current md5 hash of the returned data of the URL
- The returned data of the URL