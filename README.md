# Confluence Search Suggest Workflow for Alfred 2

Uses Confluence's REST Api for searching and fastjumping to recently viewed pages

## Installation

1. Clone this repo into your Alfred workflows directory `~/Library/Application Support/Alfred 2/Alfred.alfredpreferences/workflows/` OR [download the master branch as zip file](https://github.com/dimitri-koenig/alfred-confluence-workflow/archive/master.zip) and double click on the `confluence.search.suggest` workflow package file

2. Go into your mac keychain program, and either search for your confluence safari login data, or create such an item ([Click here for a short tutorial on that](https://www.dimitrikoenig.net/better-usage-of-sensible-user-data-for-alfred-workflows.html))

3. Make sure the "Where" fields points to the confluence installation. e.g. https://my-confluence-server.com or https://my-cloud-subscription.atlassian.net/wiki 

4. Add "com.alfredapp.dimitrikoenig.confluencesuggest" to the comment field

5. Ready

## Usage

Either type in `con {search word}` or simply `conr` for recently viewed pages