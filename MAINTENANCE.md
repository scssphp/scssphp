This file contains documentation targeted at the scssphp maintainers.

## Release process

### Prepare the release

1. Ensure that the documentation (in `docs/docs/index.md`) is up-to-date
2. Update the changelog in `docs/docs/changelog.md`
3. Update the version in `src/Version.php`
4. Update the version in `docs/_config.yml` (this should be the tag name that will be created)
5. Open a PR with these changes

### Do the release

1. Merge the PR preparing the release
2. Tag the release as `vX.Y.Z`
3. Push the tag to GitHub
4. Create a GitHub release for the tag, with the changelog in the description
