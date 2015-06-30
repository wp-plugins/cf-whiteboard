=== CF Whiteboard ===
Contributors: collindo
Tags: crossfit, wod, fitness, workout, tracking, tracker, sports, exercise, team, training, trainer, coach, athlete, gym, logbook, attendance
Requires at least: 3.5
Tested up to: 4.2.2
Stable tag: 2.5.1

CF Whiteboard turns your website and WOD Blog into a full-featured CrossFit<sup>&reg;</sup> workout tracker.

== Description ==

CF Whiteboard is a [CrossFit<sup>&reg;</sup> workout tracker for WordPress](http://cfwhiteboard.com) that integrates into your affiliate's website. It adds a Whiteboard widget to your WOD blog posts, and creates athlete profiles where your athletes can search past results, look up benchmark workouts and PRs, see charts of their progress over time, see charts of their attendance over time, and more.

We want to create a service that CrossFitters love, so don't hesitate to [email us](mailto:affiliatesupport@cfwhiteboard.com) with any questions or feedback. We have helped most of our gyms with setting up a custom Whiteboard position, adding the Athletes page to their main menu, and other wordpress-related requests. Let us know how we can help! [affiliatesupport@cfwhiteboard.com](mailto:affiliatesupport@cfwhiteboard.com)

== Installation ==

1. From your 'Plugins' menu in WordPress, click Add New and search for **CF Whiteboard**, or upload the plugin to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Check your blog for a private example post that will be created so you can try out the Whiteboard
1. The plugin will add a new Athletes page to your website. Be sure to add a link to the Athletes page in your navigation menu
1. [Email Affiliate Support](mailto:affiliatesupport@cfwhiteboard.com) if you want to chat, have any questions, or would like a product demo

== External Links ==

The whiteboard contains external links for athlete support purposes.  By installing this plugin, you give CF Whiteboard permission to embed these external links.

== Changelog ==

= 2.5.1 =
* Workaround for sites using Theme Fusion themes.

= 2.5.0 =
* Updated Whiteboard theme.

= 2.4.6 =
* Workaround a WordPress bug with WordPress export/import that caused corrupted Whiteboard data (had no effect on athlete data).

= 2.4.5 =
* Catch corrupted cookies and prevent them from breaking the Whiteboard functionality.

= 2.4.4 =
* Password-protected blog posts now hide the Whiteboard until the blog post is unlocked by entering the password.

= 2.4.3 =
* Major improvements for popups on mobile.  Affects sign in / sign up / forgot password / track workout / edit profile.

= 2.4.2 =
* Compatibility improvements for Password Protected athletes area, Prototype.js, "Paid Memberships Pro" plugin, SSL.

= 2.4.1 =
* Allow decimal places in the "seconds" field when entering your time in the Whiteboard.

= 2.4 =
* Athlete photos! Athletes can upload a profile photo.
* Athletes can edit their profile information (name, email, profile URL).
* Whiteboard "Remember Me" feature so athletes don't have to type their email so often on their personal computers.

= 2.3 =
* No more name field required when adding entries in the Whiteboard. Name comes from your profile.
* Sort the Whiteboard in order of athlete results.

= 2.2.11 =
* New & improved mobile support on the athlete profiles for signing in, signing up, and tracking workouts.
* Updated to support WordPress 3.9

= 2.2.10 =
* Highlight the CrossFit Games section to remind coaches to use it!

= 2.2.9 =
* New Whiteboard setting for more control over where the Whiteboard appears on your blog.
* Minor bugfix.

= 2.2.8 =
* Optimized for WordPress 3.7
* Improved compatibility with third-party plugins and themes.
* Ended official support for WordPress versions 3.4.X and below (version 3.5 was released December 2012). Please contact us if you need help upgrading to the latest and greatest WordPress.

= 2.2.6 =
* Added ability for athletes to EDIT and DELETE workouts they tracked individually (from their profile).
* Improve Athlete popups compatibility across websites (experiencing CSS conflicts on at least one customer's site).
* Fix bug with weightlifting repcount dropdown (for choosing 1RM, 2RM, etc.) on iPad / iPhone.
* Fix bug that prevented athletes from entering a decimal place on weightlifting benchmarks.

= 2.2 =
* Athlete login system.
* Athletes can track custom workouts, and add PRs directly to their profiles.
* Athletes can delete an accidental Whiteboard entry.
* System for confirming new athletes before creating a separate profile (to avoid duplicate profiles due to email typos).
* Improved IE performance for the Whiteboard.
* Better support for mobile phone keyboards (numeric vs. text).

= 2.1 =
* The workout date now defaults to the "publish date" of the post, unless you customize it.
* When you insert the workouts into your post, some text will be added at the bottom reminding your athletes to "Post your scores to the Whiteboard."
* Improved support for exporting posts and importing into another WP blog with the Whiteboards intact.

= 2.0 =
* Brand new athlete profiles with redesigned logbooks and complete benchmarks integration, including charts and leaderboards.

= 1.81 =
* Add a setting that allows you to hide our CrossFit-related branding. CF benchmark workouts are still available for use.

= 1.80 =
* Get ready for the 2013 Open workouts!
* New design for date field to make it more noticeable.
* Work around a collision with the "Comprehensive Google Map Plugin".
* Add a setting to disable meta box on custom post types.
* Improve data handling to allow more whiteboards to be displayed on one page.
* Other minor improvements for IE.

= 1.79 =
* Fix minor issues with the settings page not displaying new settings until page is refreshed.
* Fix issue affecting query vars and rewrite rules on multisite installations.

= 1.78 =
* Rework coach's admin area on Add Post page. Improved UI for easier & faster use.
* If an athlete visits your gym and uses the Whiteboard, the Whiteboard will now link to their profile (on their home gym's website).
* Fix scrollbar when Whiteboard is first loaded.
* Improve coach's admin experience on Internet Explorer.

= 1.77 =
* Fix issue where Weightlifting Benchmarks won't submit correctly unless you change the units.

= 1.76 =
* Fix 2 minor issues: embedded whiteboards are not being displayed, non-breaking JS error on pages without whiteboards.

= 1.75 =
* ALL NEW: Track benchmark workouts.  Coming next: All new athlete profiles with full integration of benchmark workouts and weightlifting PRs.
* Better whiteboard usability on mobile devices.
* Fix visual issue with "share on facebook" dialog in whiteboards with 3+ components.
* Faster loading of whiteboard entries (and faster page-loads).
* Minor improvements and minor bug fixes.

= 1.65 =
* Fixed a conflict with the WP Jetpack plugin.

= 1.64 =
* Added date field for workouts, so people posting the night before can make sure the correct date shows up on the athlete profiles.
* You can now press ENTER in the Whiteboard notes field.

= 1.63 =
* Fixed bug with managing the example post that is generated.

= 1.62 =
* Add facebook integration so athletes can share their results on facebook.

= 1.61 =
* Revamp the affiliate sign-up process.

= 1.60 =
* Fixed bug with editing Whiteboard entries in IE9.

= 1.59 =
* Major improvements to athlete profile navigation and paging/scrolling.
* When searching athlete profiles, workouts they didn't do are now hidden by default, with an option to show them.

= 1.58 =
* Fix bug with Developer Mode that prevented the Athletes Page from working correctly.

= 1.57 =
* Fixes for IE so that special characters like '+' and '%' don't get removed from the athlete's notes.

= 1.56 =
* Upgrade athlete page permalinks to work better with WPMU for Sites As RX.

= 1.55 =
* Add security for editing Whiteboard entries.

= 1.54 =
* Better documentation / help messages.

= 1.53 =
* Improve the installation process to avoid some problems that can occur.
* Now installs into Live Mode by default, with Developer Mode as an option.

= 1.52 =
* Performance upgrades, especially for older web browsers.

= 1.51 =
* Fix small bug where editing entries could create a duplicate entry.

= 1.50 =
* Add ATHLETES PAGE and SEARCH.

= 1.25 =
* Fix conflict with a client theme due to jQuery.important plugin.

= 1.24 =
* Fixed a typo.

= 1.23 =
* Add option for embedding the Whiteboard instead of having to click the button.

= 1.22 =
* Fix CSS bug in IE9.

= 1.20 =
* Improved Whiteboard positioning on iOS.
* Update whiteboard positioning on button mouseover in case the page has rearranged due to images loading, etc.

= 1.19 =
* Whiteboard button looks more like a button.

= 1.18 =
* Whiteboard now works well in narrow post feeds or sidebars.

= 1.17 =
* Improved feedback for WP Admins when writing the post.
* Added help messages on Add/Edit Post page.

= 1.15 =
* Added a CF Whiteboard meta box on the Add Post page for specifying the workouts each day.
* Whiteboard widget now has support for multiple classes, and/or multiple components for each class.
* Dropped support for categories.

= 1.10 =
* Fixed issue in Internet Explorer where youtube videos would cover up the whiteboard.
* Fixed issue in Internet Explorer where long entries would mess up the column widths in the whiteboard list.

= 1.9 =
* Added scrollbar to whiteboard.
* Improved scrolling on iPhone/iPad.
* Athletes can now close the whiteboard by clicking elsewhere on the page.
* Fixed issue where double-clicking the Submit button will create two entries.

= 1.8 =
* Improved support for older browsers.

= 1.6 =
* Added **Print** button for WP admin users.

= 1.5 =
* Improved styling compatibility.

= 1.4 =
* Added settings to control which post categories the whiteboard is attached to.

= 1.3 =
* Added support for older versions of WordPress (< 3.2).

= 1.2 =
* Added **Preview Mode** so affiliates can try out the whiteboard before letting their athletes see it.
* Added settings for custom whiteboard positioning.

== Upgrade Notice ==

= 2.2 =
Version 2.2 brings a new athlete login system that allows athletes to track workouts they did on their own, and makes it easier for athletes to keep their PRs up-to-date. Please upgrade immediately so your athletes can take advantage of these new features.

= 1.55 =
CF Whiteboard now has security for editing Whiteboard entries. Starting Monday, June 11, athletes will not be able to edit their entries until you upgrade the CF Whiteboard plugin to version 1.55 or newer.
