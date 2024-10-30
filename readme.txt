=== Mode Analytics ===
Contributors: modeanalytics, danielbachhuber
Tags: analytics, mode, mode analytics, embed, white-label embed, embedded analytics, data visualization
Requires at least: 4.4
Tested up to: 4.9
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Embedded analytics and reporting that deliver secure data to customers. 

== Description ==

Simple and secure embedded analytics.

= About Mode =

Mode’s [White-Label Embeds](https://about.modeanalytics.com/embedded-analytics/) make it easy to integrate custom, interactive analytics directly into your WordPress site.

Simply connect Mode to your internal data warehouse and write SQL to define the data and metrics you want to present to your customers, partners, or employees. 

Then create and configure interactive charts, PDF and CSV exports, or completely custom visualizations to deliver insights from your WordPress site.

Mode takes care of the hassles of connecting your data infrastructure, from querying to visualizing and reporting. It also handles user authentication for you, to ensure that you’re serving the right data to the right people. Check out the [guide to getting started with White-Label Embeds](https://help.modeanalytics.com/articles/guide-to-white-label-embeds/) to learn more about how they work. 

= Key Features of Mode’s White-Label Embeds =

* Interactive charts
* Completely configurable visualizations and reporting
* Supports both SQL- and Python-based data analysis 
* User authentication ensures you’re serving the right data to the right people
* Manage database load by setting rules around data freshness
* Embed a CSV or PDF download option
* Define themes and color palettes to blend analytics seamlessly into your Wordpress site

Technical documentation of these features can be [found here](https://help.modeanalytics.com/articles/setting-up-white-label-embeds/).

= Popular Uses =

* Customer-facing user stats pages
* Analytics portal for partner programs
* Program performance data delivery for marketing agencies
* KPI reporting for internal wikis

= Recommended Plugin =

* [User Switching](https://wordpress.org/plugins/user-switching/) by John Blackbourn - This plugin allows you to quickly swap between user accounts in WordPress. This is handy for administrators who need to switch between multiple accounts to manage page reports and see what end-users are viewing. 

= Configuring a Mode Report for Embedding in WordPress = 

To embed Mode reports in your WordPress site:

1. From any report in Mode, turn on the White-Label Embed toggle.
2. Enter your Mode authentication credentials and the report's URL on the plugin's settings page.
3. Add a unique token to your report to dynamically change the content of the report based on who's viewing it.
4. Add embed to pages on your WordPress site using a short code like this: `[mode-analytics report_name="-insert your report name here-"]`

See installation instructions for full configuration details.

== Installation ==

First, log in to your WordPress dashboard. Once you've done so:

1. In the left navigation, click “Plugins”, then click “Add New”.
2. Search for “Mode Analytics” — the latest version will appear at the top of the list of results.
3. Click the “Install Now” / “Download” button.
4. Wait for the installation to finish, then click the “Activate” button.

After installation, head to “Mode Analytics” under “Settings” to configure your reports.

== Frequently Asked Questions ==

= Do I need a Mode Analytics account? =
Yes. If you haven’t already created an account, please visit our [website] (https://about.modeanalytics.com/embedded-analytics) to get started by requesting a demo of White-Label Embeds. You can also email us at hi@modeanalytics.com or click on the chat icon in the product to talk with our team.

= How do I create analysis in Mode? =
Mode integrates SQL, Python, and data visualization in one platform. You'll experience the flexibility to work in familiar analytical formats, while enabling robust exploration for anyone viewing reports. 

= Can I customize reports and dashboards? =
Yes. You can customize background colors, fonts, color palettes, themes and other visual elements in your dashboard and charts. Whether you use HTML, CSS, Javascript or Python libraries, you can tailor any report to meet your needs. Check out the following links to learn more about our custom [dashboards](https://help.modeanalytics.com/articles/create-advanced-layouts-and-visualizations/ (https://blog.modeanalytics.com/introducing-mode-dashboards/)), [reports] (https://help.modeanalytics.com/articles/setting-color-palettes/ (https://blog.modeanalytics.com/custom-color-palettes/)), and our [HTML editor](https://community.modeanalytics.com/gallery/#charts/).

= Can I make my reports and dashboard interactive? =
Yes. You can enable report filters to a dashboard's data to explore trends, without loading your database.

= How do I set-up White-Label Embeds for my WordPress site? =
To set up White-Label Embeds on your site, you’ll need to enable White-Label Embeds for your organization and for the reports you want to embed. Then, update your application to create URLs for your embeds and sign those embeds. For step-by-step instructions, follow this [link](https://help.modeanalytics.com/articles/setting-up-white-label-embeds/).

= How do I render my reports and dashboards dynamically for different customers? =
White-Label Embeds are able to use user-specific parameters to render the data in the report dynamically - giving you control over the data each viewer sees, without having to create different reports for different customers. To learn more, follow this [link](https://help.modeanalytics.com/articles/guide-to-white-label-embeds/).

= Where can I get a step-by-step guide on how to use the Mode Analytics Plugin? =
For more detailed instructions, guidelines and workarounds, visit our [Mode Support Site] (https://about.modeanalytics.com/embed-reports-in-wordpress). 

= What types of databases are compatible with Mode? =
Mode can connect to all types of databases, such as MemSQL, Redshift, BigQuery, MySQL, and more! Mode can connect to databases hosted on private networks or on private machines, databases hosted in VPCs and VPNs, and databases hosted in the cloud by third parties such as Amazon and Microsoft. To learn more about which data sources we work with, click [here](https://about.modeanalytics.com/data-sources/).

= What are my options for connecting my database to Mode? =
Mode offers two connection options: Direct Connect (https://help.modeanalytics.com/articles/how-to-connect-your-cloud-database/) and Bridge (https://help.modeanalytics.com/articles/connect-with-bridge/). This document (https://help.modeanalytics.com/articles/choosing-how-to-connect/) outlines which option is best for your database. For more technical details about how Mode connects to databases, see these documents on the architecture (https://help.modeanalytics.com/articles/bridge-connector-architecture-and-security-faqs/) and security (https://about.modeanalytics.com/security/) of Mode connector options. 


== Screenshots ==

1. From any report in Mode, turn on the White-Label Embed toggle.
2. Enter your Mode authentication credentials and the report's URL on the plugin's settings page.
3. Add a unique token to your report to dynamically change the content of the report based on who's viewing it.
4. Add embed to pages on your WordPress site using a short code.
5. View your White-Label Embed.

== Changelog ==

= 0.1.0 (March 6th, 2018) =
* Initial release.
