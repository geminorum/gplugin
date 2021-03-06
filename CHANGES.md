### 41
* networkcore: setup network before hooks

### 40
* admincore: append component to page slug
* session: disabled
* settings: :new: email setting type
* settings: default regular textareas
* settings: support multiple text fields
* settings: rtl class for quicktags
* taxonomyhelper: using term cache for post objects
* wphelper: :warning: fixed notice

### 39
* http: more checks on ip
* utils: unslash rethinking
* utils: make sure data is an array on same key
* network: static settings subs moved

### 38
* html: new dropdown helper
* settings: correct check if exclude is an array
* settings: cpt/tax names along with labels
* taxonomyhelper: :up: get terms
* texthelper: seperator in name reformatter
* wphelper: thickbox for featured image

### 37
* all: using wp hash
* classcore: internal methods updated
* html: new helpers
* number: :new: new class
* settings: new field type for taxonomies
* wphelper: check for posttype label
* wphelper: static caching slug lookups

### 36
* settings: :new: class
* network: schedule events hook
* settingscore: moving up setting field generator
* taxonomyhelper: extra args for get terms
* taxonomyhelper: not updating term meta cache
* wphelper: extra args for get users

### 35
* all: dropping deprecated
* networkcore: setting up settings after constants
* classcore: log objects
* componentcore: auto hook late settings
* plugincore: discard draft
* html: switch to html class for tag generation
* html: switch to html class for linking stylesheets
* html: notice api
* utils: correct check for needle in the haystack!
* settingscore: posttypes field
* settingscore: avoiding default overrides
* settingscore: check if no checkbox selected
* wphelper: new posttypes helper
* wphelper: check for cap before flush conditional

### 34
* admincore: correct url for settings style
* html: tag generator
* html: wrapper for esc_url
* html: sanitizing phone numbers
* http: deprecated `gPluginWPRemote`, moved here
* texthelper: reformat name helper
* texthelper: unused removed

### 33
* classcore: wrapper check for errors
* wphelper: support single term edit link
* wpremote: new `gPluginWPRemote`
* html: new `gPluginHTML`
* http: new `gPluginHTTP`

### 32
* factory: using calss & exceptions
* classcore: log checks for arrays
* classcore: duplicate factory helper removed
* admincore: enqueue helper
* admincore: admin print style hook removed
* modulecore: default current screen hook
* formhelper: link stylesheet accepts array of query vars
* utils: moving up dump helper
* utils: ip padding for more readability

### r31
* wphelper: min WP version
* wphelper: get users by blog or network
* utils: same key checks for false & null

### r30
* gPluginTermMeta: disabled

### r29
* gPluginSettingsCore: almost rewrite
* more camel casing

### r28
* passing revision to the plugin callback
* gPluginListTableCore: deprecated

### r27
* gPluginTermMeta: deprecated
* all extending from class core

### r26

### r25

### r23
* converted to an actual WordPress plugin

### r22

### r21
* using [GitHub Updater](https://github.com/afragen/github-updater)
* cleanup

### r20
* first public release
