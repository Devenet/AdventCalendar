function download(filename, content) {
  var blob = new Blob([content], {type: "text/plain;charset=utf-8"});
  saveAs(blob, filename);
}

function Day(day) {
  var self = this;
  self.day = day;
  self.title = '';
  self.legend = '';
  self.text = '';
  self.link = '';
  self.isEmpty = function() {
    return self.title.trim().length==0 && self.legend.trim().length==0 && self.text.trim().length==0 && self.link.trim().length==0; 
  }

  self.toObject = function() {
    var o = {};
    if (self.title.trim().length > 0) { o['title'] = self.title; }
    if (self.legend.trim().length > 0) { o['legend'] = self.legend; }
    if (self.text.trim().length > 0) { o['text'] = self.text; }
    if (self.link.trim().length > 0) { o['link'] = self.link; }
    return o;
  }
}

function Settings() {
  var self = this;
  self.title = ko.observable('Advent Calendar · ' + (new Date()).getFullYear());
  self.year = ko.observable(new Date().getFullYear().toString());
  self.month = '';
  self.first_day = '';
  self.last_day = '';
  self.background = false;
  self.lang = 'en';
  self.passkey = '';
  self.disqus_shortname = '';
  self.url_rewriting = false;
  self.google_analytics =  { tracking_id: '', domain: '' };
  self.piwik = { piwik_url: '', site_id: '' };
  self.plausible = { domain: '', custom_src: '' };

  self.isValidTitle = ko.computed(function() { return self.title().trim().length > 0; });
  self.isValidYear = ko.computed(function() { return self.year().trim().length > 0; });
  self.isValid = ko.computed(function() { return self.isValidTitle() && self.isValidYear(); });

  self.toObject = function() {
    var o = {
      title: self.title(),
      year: self.year()
    };
    if (self.month.trim().length > 0) { o['month'] = self.month; }
    if (self.first_day.trim().length > 0) { o['first_day'] = self.first_day; }
    if (self.last_day.trim().length > 0) { o['last_day'] = self.last_day; }
    if (self.background) { o['background'] = 'alternate'; }
    var lang = self.lang.trim();
    if (lang !== 'en' && ['fr', 'de'].indexOf(lang) > -1) { o['lang'] = lang; }
    if (self.passkey.trim().length > 0) { o['passkey'] = self.passkey; }
    if (self.disqus_shortname.trim().length > 0) { o['disqus_shortname'] = self.disqus_shortname; }
    if (self.url_rewriting) { o['url_rewriting'] = 'url_rewriting'; }
    if (self.google_analytics.tracking_id.trim().length > 0) { o['google_analytics'] = self.google_analytics; }
    if (self.piwik.piwik_url.trim().length > 0 && self.piwik.site_id.trim().length > 0) { o['piwik'] = self.piwik; }
    if (self.plausible.domain.trim().length > 0) {
      if (self.plausible.custom_src.trim().length == 0) {
        o['plausible'] = {'domain': self.plausible.domain};
      } else {
        o['plausible'] = self.plausible;
      }
    }
    return o;
  };
}

function GeneratorViewModel() {
  var self = this;

  self.selectedGenerator = ko.observable(window.location.hash == '#settings' ? 'settings.json' : 'calendar.json');
  self.availableGenerators = ko.observableArray(['calendar.json', 'settings.json']);
  self.generatorChanged = function() {
    window.location.hash = '#' + self.selectedGenerator().replace('.json', ''); 
  };
  self.generatorChanged();
  window.addEventListener('hashchange', function() {
    generator = window.location.hash.substring(1) + '.json'; 
    if ($.inArray(generator, self.availableGenerators()) > -1) {
      self.selectedGenerator(generator);
    }
  }, false);

  self.settings = ko.observable(new Settings());

  _days = [];
  for(i=1; i<=31; i++)
  {
    _days.push(new Day(i));
  }
  self.days = ko.observableArray(_days);

  
  self.enableDownload = ko.computed(function() {
    return (self.selectedGenerator() == 'settings.json' && self.settings().isValid()) || self.selectedGenerator() == 'calendar.json';
  });

  self.download = function() {
    var result = {};

    switch(self.selectedGenerator()) {
      case 'calendar.json':
        for (i=0, l=self.days().length; i<l; i++) {
          day = self.days()[i];
          if (!day.isEmpty()) {
            result[day.day] = day.toObject();
          }
        }
        download('calendar.json', JSON.stringify(result));
        break;

      case 'settings.json':
        if (!self.settings().isValid()) {
          console.log(self.isValidTitle(), self.isValidYear());
          break;
        }
        download('settings.json', JSON.stringify(self.settings().toObject()));
        break;

      default:
        console.error('File "' + this.selectedGenerator() + '" is not supported.');
        break;
    }
  };

  self.clear = function() {
    $('.row-day input').val('');
    $('.row-day textarea').val('');
  }

  self.top = function() {
    $('html,body').animate({scrollTop: 0}, 'slow');
  }
}

ko.bindingHandlers.placeholder = {
    init: function (element, valueAccessor, allBindingsAccessor) {
        var underlyingObservable = valueAccessor();
        ko.applyBindingsToNode(element, { attr: { placeholder: underlyingObservable } } );
    }
};
ko.applyBindings(new GeneratorViewModel());