#AdventCalendar

Advent Calender is a very light web application to show a photo and its legend per day before Christmas.  
It’s written in PHP, use last web technologies, and proud to not support old versions of IE.


***

## Installation

Rename file `settings.example.json` file in folder `private` in `settings.json` and personalize information.

If you want to use the alternate background, juste add this line `"background": "alternate"` before the last `}` and don't forget to add a comma at the end of the previous line!

## Configuration

###How to configure it?

Put your photos in the `private/` folder, and name them with the number of the day you want to illustrate.
For example, for the 1st December, call your file `1.jpg` or `1.jpeg`.

To add a title, a legend or a text on a day page, just rename `calendar.example.json` in folder `private` in `calendar.json` and add what you want to display.

For example:

```json
{
        "1": {
                "title": "First day of December",
                "legend": "Paris, November 2013",
                "text": "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat."
        },
        "2": {
                "legend": "Berlin, March 2013"
        },
        "6": {
                "title": "Saint Nicholas Day"
        },
        "12": {
                "text": "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."
        }
}
```

###Oups, problems?

- __All days are shown before Christmas:__ Check the syntax in `settings.json` or update the year.
- __Photo is not displayed:__ Be sure your photo is correctly named, like `3.jpg` or `12.jpeg`.
- __Title, legend or text are not displayed:__ Check the syntax of your `calendar.json` file.
- __Day is shown in late or advance:__ Configure the timezone of your server.


###Want to contribute?

Source code is hosted on [Github](https://github.com/nicolabricot/AdventCalendar) by nicolabricot. Fell free to fork it and to improve the application :)

