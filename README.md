#AdventCalendar

Advent Calendar is a very light web application to show a picture and its legend per day before Christmas.  
It’s written in PHP, use last web technologies, and proud to not support old versions of IE.


![Home of Advent Calendar](adventcalendar.jpg)

***

## You are in a hurry?

OK, let's do it quickly!

```
git clone https://github.com/nicolabricot/AdventCalendar advent
cd advent/private
cp settings.example.json settings.json
```

Then edit the `settings.json` file to configure the application and set the year.
To finish, just put your JPEG files in the `private` folder; images named with the number of the day (such as `1.jpeg` or `2.jpg`).

***

## Installation

### Get the source

Download the last version on the [releases page](https://github.com/nicolabricot/AdventCalendar/releases)!

### Or clone the repository

If you have git on your server, you can also just clone the repository with:

```
git clone https://github.com/nicolabricot/AdventCalendar advent
```

## Configuration

### Basic configuration

Rename the `settings.example.json` file on folder `private` in `settings.json` and edit it to configure the application.

The minimum configuration file must be like:
```json
{
	"title": "my Advent Calendar",
	"year": 2014
}
```

### Full available options

| Property | Type | Description |
| --- | --- | --- |
| __`year`__ | integer | Set the year to be used for the calendar and the cutdown |
| __`title`__ | string | Set the title of your AdventCalendar |
| __`background`__ | string | Set to `alternate` to use an alternative background image |
| __`passkey`__ | string | If filled out visitors need to enter a password to access the private AdventCalendar |
| __`disqus_shortname`__ | string | Set a Disqus account to enable comments for days |
| __`google_analytics`__ | object | Set a Google Analytics account with a child object containing the two properties `tracking_id` and `domain` |
| __`piwik`__ | object | Set a Piwik account with a child object containing the two properties `piwik_url` and `site_id` |

This is an example with all options:
```json
{
	"title": "my Advent Calendar",
	"year": 2014,
	"background": "alternate",
	"passkey": "mySecretPassword",
	"disqus_shortname": "myDisqusName",
	"google_analytics": {
		"tracking_id": "UC-12345",
		"domain": "domain.tld"
	},
	"piwik": {
		"piwik_url": "piwik.domain.tld",
		"site_id": "12345"
	}
}
```

### Transform AdventCalendar into CountDownCalendar

If you want, you can also customize month, first day and last day which are used to display the period of days, but it's not really an AdventCalendar anymore ;-)

Just change the period with those 3 options:
```json
{
	"month": 3,
	"first_day": 8,
	"last_day": 31
}
```

## Picture per days

### Add pictures

Put your photos in the `private/` folder, and name them with the number of the day you want to illustrate.
For example, for the 1st December, call your file `1.jpg` or `1.jpeg`.  

__Be sure that the access to `private` folder is forbidden when browsing it!__   
For Apache configuration, be sure that a `.htaccess` file with the directive `deny from all` is in and read.

### Customize legend and title

To add a title, a legend or a text on a day page, just rename `calendar.example.json` in folder `private` in `calendar.json` and add what you want to display.

For example:

```json
{
        "1": {
                "title": "First day of December",
                "legend": "Paris, November 2013",
                "text": "Lorem ipsum dolor sit amet, [...]."
        },
        "2": {
                "legend": "Berlin, March 2013"
        },
        "6": {
                "title": "Saint Nicholas Day"
        },
        "12": {
                "text": "Lorem ipsum dolor sit amet, [...]."
        }
}
```

![A day with title, legend and text](adventcalendar-day.jpg)

***

## Oups, problems?

- __All days are shown before Christmas:__ Check the syntax in `settings.json` or update the year.
- __Photo is not displayed:__ Be sure your photo is correctly named, like `3.jpg` or `12.jpeg`.
- __Title, legend or text are not displayed:__ Check the syntax of your `calendar.json` file.
- __Day is shown in late or advance:__ Configure the timezone of your server.


## Want to contribute?

Source code is hosted on [Github](https://github.com/nicolabricot/AdventCalendar) by [nicolabricot](http://nicolabricot.com). Feel free to fork it and to improve the application!

Let me know if you use Advent Calendar by sending me an email, I will be happy ;-)

