"use strict";

/**
 * Given an associative array (aka a map), convert it into a string of parameters
 * for a URI. Does not prepend the query with a '?'.
 * 
 * @param {object} arr The associative array to use
 * @return {string} A string of parameters i.e. &name=toyota&make=idontknowaboutcars
 */
function mapToQuery(arr) {
    var query = [];
    for(var key in arr) {
        query.push(encodeURIComponent(key) + '=' + encodeURIComponent(arr[key]));
    }
    return query.join('&');
}

var ajax = {
    send: function(url, callback, method, payload, sync) {
        var xhr = new XMLHttpRequest();
        xhr.open(method, url, sync);
        xhr.onreadystatechange = function() {
            if(xhr.readyState === XMLHttpRequest.DONE) {
                callback(xhr.responseText);
            }
        };
        if(method === "POST" || method == "PUT") {
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        }
        xhr.send(payload);
    },

    /**
     * Create a new GET request via AJAX.
     *
     * @param {string} url URL of the API
     * @param {object} params An associative array of the query e.g. { brand: "volvo", make: "something" }
     * @param {function} callback What to do when the AJAX request succeeds
     * @param {boolean} sync Whether or not to use asynchronous AJAX
     */
    get: function(url, params, callback, sync) {
        if(params) {
            url += '?' + mapToQuery(params);
        }
        this.send(url, callback, "GET", null, sync);
    },

    /**
     * Create a new POST request via AJAX.
     *
     * @param {string} url URL of the API
     * @param {object} params An associative array of the query e.g. { brand: "volvo", make: "something" }
     * @param {function} callback What to do when the AJAX request succeeds
     * @param {boolean} sync Whether or not to use asynchronous AJAX
     */
    post: function(url, params, callback, sync) {
        this.send(url, callback, "POST", mapToQuery(params), sync);
    }, 

    del: function(url, params, callback, sync) {
        this.send(url, callback, "DELETE", mapToQuery(params), sync);
    },

    put: function(url, params, callback, sync) {
        this.send(url, callback, "PUT", mapToQuery(params), sync);
    }
};
