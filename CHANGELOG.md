
## Current
 * 2021-03-08 12:03:32 | we use an overflow for the product name [Tristan Hofman (baldwinonlightspeed)]
 * 2021-03-05 16:03:31 | fixed issue with shipments of bundled products [Tristan Hofman (baldwinonlightspeed)]

## v1.0.8
 * 2021-03-05 14:03:07 | we strip leading zeros [Tristan Hofman (baldwinonlightspeed)]

## v1.0.7
 * 2021-03-05 09:03:29 | we want to group the simples. Multiple bundles/configurables can have the same simple defined [Tristan Hofman (baldwinonlightspeed)]

## v1.0.6
 * 2021-03-04 11:03:22 | updated changelog [Tristan Hofman (baldwinonlightspeed)]
 * 2021-03-04 11:03:49 | only configurable products have childern items.. [Tristan Hofman (baldwinonlightspeed)]

## v1.0.5
 * 2021-03-04 11:03:32 | updated changelog [Tristan Hofman (baldwinonlightspeed)]
 * 2021-03-04 11:03:06 | hotix, missing dependency + we only want to send simple product info [Tristan Hofman (baldwinonlightspeed)]

## v1.0.4
 * 2021-03-04 10:03:47 | updated changelog [Tristan Hofman (baldwinonlightspeed)]
 * 2021-02-25 14:02:23 | SPORT-505 removed unused node package.json [Tristan Hofman (baldwinonlightspeed)]
 * 2021-02-25 14:02:01 | SPORT-505 removed unused class [Tristan Hofman (baldwinonlightspeed)]
 * 2021-02-25 14:02:55 | SPORT-505 fixes another incompatible change done by magento in 2.3.6-p2 [Tristan Hofman (baldwinonlightspeed)]

## v1.0.3
 * 2021-01-18 09:01:49 | Update CHANGELOG.md [Duckↄhip]
 * 2021-01-18 09:01:18 | updated general files [Tristan Hofman (baldwinonlightspeed)]
 * 2021-01-18 09:01:28 | installed markdown-pdf + updated readme file [Tristan Hofman (baldwinonlightspeed)]
 * 2021-01-18 09:01:28 | fixed some documentation [Tristan Hofman (baldwinonlightspeed)]
 * 2021-01-18 09:01:28 | Fixed issue with breaking constructor when upgrading from 2.3.5-p2 -> 2.3.6, we're using the ObjectManager to tackle this problem. This should however be avoided in the future [Tristan Hofman (baldwinonlightspeed)]
 * 2021-01-18 09:01:28 | chore(release): 1.0.2 [Tristan Hofman (baldwinonlightspeed)]

## v1.0.2
 * 2021-01-12 10:01:57 | chore(release): 1.0.2 [Tristan Hofman (baldwinonlightspeed)]
 * 2021-01-12 10:01:22 | updated gitignore [Tristan Hofman (baldwinonlightspeed)]

## v1.0.1
 * 2021-01-12 10:01:33 | chore(release): 1.0.1 [Tristan Hofman (baldwinonlightspeed)]
 * 2021-01-12 10:01:25 | chore(release): 1.0.0 [Tristan Hofman (baldwinonlightspeed)]
 * 2021-01-12 10:01:58 | fixed typo in the footnote [Tristan Hofman (baldwinonlightspeed)]
 * 2021-01-11 19:01:57 | we need the multiply operation for the bruto calculation [Tristan Hofman (baldwinonlightspeed)]
 * 2021-01-11 19:01:55 | we want a global incoterms, also added netto/bruto for weight [Tristan Hofman (baldwinonlightspeed)]
 * 2021-01-11 14:01:18 | added signature, custom columns and footnote to invoice PDF [Tristan Hofman (baldwinonlightspeed)]
 * 2021-01-11 14:01:44 | added extra config fields for HScode and country of origin [Tristan Hofman (baldwinonlightspeed)]

## v1.0.0

[1.0.0]
 * 2020-12-23 14:12:12 | updated composer constraints, updated readme files [Baldwin on the Road 01]
 * 2020-12-23 14:12:26 | added readme pdf [Baldwin on the Road 01]
 * 2020-12-22 15:12:34 | more static test fixes [Baldwin on the Road 01]
 * 2020-12-22 14:12:36 | fixed an issue with the orderStatus api. We need to have docblocks for every parameter, otherwise di:compile fails [Baldwin on the Road 01]
 * 2020-12-22 13:12:09 | fixed issue with sending pdfs outside EU, also added config field for the site indication [Baldwin on the Road 01]
 * 2020-12-22 12:12:41 | updated readme [Baldwin on the Road 01]
 * 2020-12-18 12:12:37 | set phpstan level to 1 [Tristan Hofman]
 * 2020-12-17 17:12:46 | intermediate commit [Tristan Hofman]
 * 2020-12-17 16:12:08 | intermediate commit [Tristan Hofman]
 * 2020-12-17 16:12:39 | ran static code checks [Tristan Hofman]
 * 2020-12-17 14:12:56 | fixed issue with address overflow + retention days [Tristan Hofman]
 * 2020-12-15 17:12:15 | we add 5 invoices in 1 document [Tristan Hofman]
 * 2020-12-15 16:12:35 | added perference to make invoice pdf smaller [Tristan Hofman]
 * 2020-12-15 16:12:31 | we also return ok status for inventory mgmt [Tristan Hofman]
 * 2020-12-15 16:12:19 | added some missing returns in the inventory model [Tristan Hofman]
 * 2020-12-15 15:12:20 | optimised rendering of pdfs, we only need to render it 1 time [Tristan Hofman]
 * 2020-12-14 15:12:27 | fixes after testing session [Tristan Hofman]
 * 2020-12-14 09:12:35 | fixed typo in extension attribute of order item distrimediaref + removed prefix for servicepoint in bpost shipping [Tristan Hofman]
 * 2020-12-11 15:12:45 | we add the service point and carrier [Tristan Hofman]
 * 2020-12-11 14:12:33 | We catch all other exceptions [Tristan Hofman]
 * 2020-12-11 14:12:25 | fixed issue with products in shipment [Tristan Hofman]
 * 2020-12-01 16:12:18 | changed status 200 to OK [Tristan Hofman]
 * 2020-12-01 15:12:05 | another attempt on fixing the options [Tristan Hofman]
 * 2020-12-01 15:12:55 | fixed issue with wrong status mapping [Tristan Hofman]
 * 2020-12-01 15:12:49 | we return status ok + fix for statusses [Tristan Hofman]
 * 2020-12-01 13:12:50 | fix for multiple/single trackIDs, shippingItems [Tristan Hofman]
 * 2020-11-30 17:11:55 | added a bunch of datamodels [Tristan Hofman]
 * 2020-11-26 14:11:59 | we also update the increment ID [Tristan Hofman]
 * 2020-11-26 13:11:09 | fix in the previous commit [Tristan Hofman]
 * 2020-11-26 13:11:26 | we fetch the order with the internal magento id or the distrimedia Id [Tristan Hofman]
 * 2020-11-26 11:11:48 | We capture already synced orders [Tristan Hofman]
 * 2020-11-25 17:11:42 | we disabled the timeout [Tristan Hofman]
 * 2020-11-25 17:11:26 | Fixed issue with fetching and saving the shipping method [Tristan Hofman]
 * 2020-11-25 16:11:04 | since the shipping methods for bpost exceed the hard limit of 10 characters, we use the predefined carriers instead [Tristan Hofman]
 * 2020-11-25 16:11:53 | we need to cast the max timeout to an int [Tristan Hofman]
 * 2020-11-25 15:11:21 | we first fetch the orders with the collection, then make a call to the db per order. Not that performant, but otherwise gives issues [Tristan Hofman]
 * 2020-11-25 15:11:08 | Added option to define timout + added massaction to reschedule [Tristan Hofman]
 * 2020-11-25 13:11:40 | Added missing result param with calling array_merge [Tristan Hofman]
 * 2020-11-25 13:11:27 | added sync attempts to grid + fixed issue when fetching the invoices [Tristan Hofman]
 * 2020-10-16 14:10:57 | added missing external ref property [Tristan Hofman]
 * 2020-10-16 14:10:56 | added missing property in the payload + added missing hook for the logger [Tristan Hofman]
 * 2020-10-16 13:10:09 | Fixed issue with reuquests to check on [Tristan Hofman]
 * 2020-10-16 13:10:47 | Addd Rest Api log plugin to monitor requests sent to the distrimedia endpoints [Tristan Hofman]
 * 2020-10-14 15:10:13 | fixed an issue with wrong path for cron data [Tristan Hofman]
 * 2020-10-12 15:10:46 | Added option to only send invoices when shipping country is outside EU [Tristan Hofman]
 * 2020-10-12 12:10:52 | We only fetch the message from the exception. Stack trace is overkill [Tristan Hofman]
 * 2020-10-12 11:10:41 | added missing init of variable + fixed typo [Tristan Hofman]
 * 2020-10-12 11:10:20 | better error handling [Tristan Hofman]
 * 2020-10-09 11:10:55 | clean-up of module [Baldwin on the Road 01]
 * 2020-10-06 17:10:57 | added some fixes for msi implementation [Tristan Hofman]
 * 2020-10-02 12:10:33 | Introduced max sync attempts of orders + abstraction of the logger's template information [Tristan Hofman]
 * 2020-10-01 17:10:07 | fixed issue in id of email template [Tristan Hofman]
 * 2020-10-01 17:10:32 | fixed issue with dependency on module-sales [Tristan Hofman]
 * 2020-10-01 16:10:33 | removed obsolete observer and unit test [Tristan Hofman]
 * 2020-10-01 16:10:55 | intermediate commit [Tristan Hofman]
 * 2020-09-30 17:09:37 | Initial commit [Tristan Hofman]
