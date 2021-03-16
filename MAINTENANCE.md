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

## Update of the sass-spec repository

Our testsuite is using a pinned version of the official [sass-spec](https://github.com/sass/sass-spec) repository, as skipped tests are maintained in our own testsuite rather than by marking tests as todo upstream (because we are not an official implementation). This pinned version should be updated regularly to ensure that we benefit from sass-spec updates.

1. Update the repository definition in the `composer.json`:
    1. Update the commit reference. This appears 3 times (the `reference` fields for `dist` and `source`, and in the source URL).
    2. Update the version number. The version is set based on the date of the commit.
2. Run `make rebuild-sass-spec` to update the list of excluded cases
3. Review changes to the exclude lists, if any (in case they uncover something bad)
4. Submit the updated config in a PR
