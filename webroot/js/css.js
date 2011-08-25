!function(win, doc, timeout) {
  var head = doc.getElementsByTagName("head")[0],
      list = {}, ids = {}, delay = {},
      links = {}, s = 'string', f = false,
      push = 'push', domContentLoaded = 'DOMContentLoaded', readyState = 'readyState',
      addEventListener = 'addEventListener', onreadystatechange = 'onreadystatechange',
      every = function(ar, fn) {
        for (var i = 0, j = ar.length; i < j; ++i) {
          if (!fn(ar[i])) {
            return f;
          }
        }
        return 1;
      };
      function each(ar, fn) {
        every(ar, function(el) {
          return !fn(el);
        });
      }

  if (!doc[readyState] && doc[addEventListener]) {
    doc[addEventListener](domContentLoaded, function fn() {
      doc.removeEventListener(domContentLoaded, fn, f);
      doc[readyState] = "complete";
    }, f);
    doc[readyState] = "loading";
  }

  var $css = function(paths, idOrDone, optDone) {
    paths = paths[push] ? paths : [paths];
    var idOrDoneIsDone = idOrDone && idOrDone.call,
        done = idOrDoneIsDone ? idOrDone : optDone,
        id = idOrDoneIsDone ? paths.join('') : idOrDone,
        queue = paths.length;
        function loopFn(item) {
          return item.call ? item() : list[item];
        }
        function callback() {
          if (!--queue) {
            list[id] = 1;
            done && done();
            for (var dset in delay) {
              every(dset.split('|'), loopFn) && !each(delay[dset], loopFn) && (delay[dset] = []);
            }
          }
        }
    timeout(function() {
      each(paths, function(path) {
        if (links[path]) {
          id && (ids[id] = 1);
          callback();
          return;
        }
        links[path] = 1;
        id && (ids[id] = 1);
        create($css.path ?
          $css.path + path + '.css' :
          path, callback);
      });
    }, 0);
    return $css;
  };

  function create(path, fn) {
    var el = doc.createElement("link"),
        loaded = f;
    el.onload = el.onerror = el[onreadystatechange] = function () {
      if ((el[readyState] && !(/^c|loade/.test(el[readyState]))) || loaded) {
        return;
      }
      el.onload = el[onreadystatechange] = null;
      loaded = 1;
      fn();
    };
    el.async = 1;
	el.setAttribute("rel", "stylesheet")
	el.setAttribute("type", "text/css")
	el.setAttribute("href", path)

    head.insertBefore(el, head.firstChild);
  }

  $css.get = create;

  $css.ready = function(deps, ready, req) {
    deps = deps[push] ? deps : [deps];
    var missing = [];
    !each(deps, function(dep) {
      list[dep] || missing[push](dep);
    }) && every(deps, function(dep) {
      return list[dep];
    }) ? ready() : !function(key) {
      delay[key] = delay[key] || [];
      delay[key][push](ready);
      req && req(missing);
    }(deps.join('|'));
    return $css;
  };

  var old = win.$css;
  $css.noConflict = function () {
    win.$css = old;
    return this;
  };

  (typeof module !== 'undefined' && module.exports) ?
    (module.exports = $css) :
    (win['$css'] = $css);

}(this, document, setTimeout);