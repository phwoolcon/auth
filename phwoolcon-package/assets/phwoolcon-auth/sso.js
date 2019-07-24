/*! phwoolcon sso.js v1.0-dev https://github.com/phwoolcon/auth | Apache-2.0 */
/* SSO api */
!function (w, d) {
    w.$p || (w.$p = {
        options: {
            ssoCheckUri: "sso/check",
            ssoServerCheckUri: "sso/server-check",
            ssoServerGetUid: "sso/uid",
            baseUrl: "/"
        }
    });

    var undefined, sso, options, simpleStorage, initialized, body, cIframe, sIframe, clientWindow, serverWindow,
        notifyForm, msgTargetOrigin, timerServerCheck, vars = {}, console = w.console, JSON = w.JSON, fetch = w.fetch,
        localStorage = w.localStorage, debugTag = $p.options.isSsoServer ? "[Server]" : "[Client]",
        ssoClientNotifyIframeName = "_sso_client_iframe_" + (+new Date);
    options = {
        ssoServer: $p.options.baseUrl,
        ssoCheckUri: $p.options.ssoCheckUri,
        ssoServerCheckUri: $p.options.ssoServerCheckUri,
        ssoServerGetUid: $p.options.ssoServerGetUid,
        initToken: "",
        initTime: 0,
        notifyUrl: "",
        debug: false
    };
    simpleStorage = w.simpleStorage || {
        get: function (key) {
            return _getJson(localStorage.getItem("_sso_" + key));
        },
        set: function (key, value) {
            return localStorage.setItem("_sso_" + key, _jsonStringify(value));
        }
    };
    sso = w.$p.sso = {
        options: options,
        init: function (ssoOptions) {
            sso.setOptions(ssoOptions);
            if (initialized) {
                return;
            }
            initialized = true;
            body = d.getElementsByTagName("body")[0];
            if ($p.options.isSsoServer) {
                msgTargetOrigin = d.referrer;
                _listen(w, "message", function (e) {
                    _serverOnMessage.apply(sso, [e]);
                });
            } else {
                msgTargetOrigin = options.ssoServer;
                _listen(w, "message", function (e) {
                    _clientOnMessage.apply(sso, [e]);
                });
                cIframe = d.createElement("iframe");
                cIframe.width = cIframe.height = cIframe.frameBorder = 0;
                cIframe.style.display = "none";
                cIframe.name = ssoClientNotifyIframeName;
                cIframe.src = "";
                body.appendChild(cIframe);

                var notifyField = d.createElement("input");
                notifyField.type = "hidden";
                notifyField.name = "sso_user_data";
                notifyForm = d.createElement("form");
                notifyForm.action = options.notifyUrl;
                notifyForm.style.display = "none";
                notifyForm.method = "POST";
                notifyForm.target = ssoClientNotifyIframeName;
                notifyForm.appendChild(notifyField);
                notifyForm.notifyField = notifyField;
                body.appendChild(notifyForm);
                sso.check();
            }
        },
        setOptions: function (ssoOptions) {
            if (ssoOptions) for (var key in ssoOptions) {
                if (ssoOptions.hasOwnProperty(key)) {
                    options[key] = ssoOptions[key];
                }
            }
            return sso;
        },
        check: function () {
            _debug("Start checking");
            var clientUid = sso.getUid(),
                message = {
                    clientUid: clientUid, check: true, setOptions: {
                        debug: options.debug,
                        initToken: options.initToken,
                        initTime: options.initTime,
                        notifyUrl: options.notifyUrl
                    }
                };
            if (sIframe) {
                return serverWindow && _sendMsgTo(serverWindow, message);
            }
            sIframe = d.createElement("iframe");
            sIframe.src = options.ssoServer + options.ssoCheckUri;
            sIframe.width = sIframe.height = sIframe.frameBorder = 0;
            sIframe.style.display = "none";
            _listen(sIframe, "load", function () {
                serverWindow = sIframe.contentWindow;
                _sendMsgTo(serverWindow, message);
            });
            body.appendChild(sIframe);
        },
        stopCheck: function () {
            _sendMsgTo(serverWindow, {stopCheck: true});
        },
        getUid: function () {
            return simpleStorage.get("uid");
        },
        setUid: function (uid, ttl) {
            _debug("Set uid: " + uid);
            simpleStorage.set("uid", uid, {TTL: ttl || 0});
        }
    };

    function _clientLogin(loginData) {
        _debug("Client login");
        _debug(loginData);
        notifyForm.notifyField.value = loginData;
        notifyForm.submit();
        _debug("App notification sent");
    }

    function _clientLogout() {
        vars.clientUid && _debug("Client logout");
        sso.setUid(null);
        notifyForm.notifyField.value = "";
        notifyForm.submit();
        _debug("App notification sent");
    }

    function _clientOnMessage(e) {
        var data = _getJson(e.data);
        _debug("Handle in client");
        if (data.login) {
            _clientLogin(data.login);
        }
        if (data.setUid) {
            sso.setUid(data.setUid);
        }
        if (data.logout) {
            _clientLogout();
        }
    }

    function _debug(info) {
        options.debug && console && (console.debug ? console.debug(debugTag, info) : console.log(debugTag, info));
    }

    function _error(error) {
        console && (console.error ? console.error(debugTag, error) : console.log(debugTag, error))
    }

    function _getJson(data) {
        try {
            return JSON.parse(data);
        } catch (E) {
            return data;
        }
    }

    function _jsonStringify(obj) {
        return JSON.stringify(obj);
    }

    function _listen(host, eventName, callback) {
        if ("addEventListener" in host) {
            host.addEventListener(eventName, callback, false);
        } else {
            host.attachEvent("on" + eventName, callback);
        }
    }

    function _sendMsgTo(frame, message) {
        frame.postMessage(typeof message == "string" ? message : _jsonStringify(message), msgTargetOrigin);
    }

    function _serverCheck() {
        var clientUid = vars.clientUid,
            serverUid = sso.getUid();

        /**
         * @param {Response} response
         * @returns {Response}
         */
        function _checkStatus(response) {
            if (response.status >= 200 && response.status < 300) {
                return response
            } else {
                var error = new Error(response.statusText);
                error.response = response;
                throw error;
            }
        }

        /**
         *
         * @param {Response} response
         * @returns {any}
         */
        function _parseJSON(response) {
            return response.json()
        }

        function _autoCheck(interval) {
            timerServerCheck = setTimeout(_serverCheck, interval);
        }

        clientWindow = w.parent;
        if (serverUid === undefined) {
            _debug("Clarifying uid...");
            fetch(options.ssoServer + options.ssoServerGetUid, {
                credentials: "same-origin"
            }).then(_checkStatus).then(_parseJSON).then(function (data) {
                _debug(data);
                sso.setUid(data.uid, data.uidTtl * 1000);
                _serverCheck();
            }).catch(function (error) {
                _debug("Request failed:");
                _error(error);
                _autoCheck(60000)
            });
            return;
        }
        _autoCheck(1000);
        if (clientUid === serverUid) {
            return;
        }
        _debug("Server uid: " + serverUid);
        if (serverUid) {
            // Login
            _stopServerCheck();
            _debug("Login: " + serverUid);
            fetch(options.ssoServer + options.ssoServerCheckUri, {
                method: "POST",
                credentials: "same-origin",
                body: $p.jsonToFormData({
                    notifyUrl: options.notifyUrl,
                    initTime: options.initTime,
                    initToken: options.initToken
                })
            }).then(_checkStatus).then(_parseJSON).then(function (data) {
                var userData = data["user_data"];
                _debug(data);
                if (userData) {
                    _sendMsgTo(clientWindow, {login: userData});
                } else {
                    sso.setUid(serverUid = null);
                    _sendMsgTo(clientWindow, {logout: true});
                }
                vars.clientUid = serverUid;
                _autoCheck(1000);
            }).catch(function (error) {
                _debug("Request failed:");
                _error(error);
                _autoCheck(60000);
            });
            _sendMsgTo(clientWindow, {setUid: serverUid});
        } else {
            // Logout
            clientUid && _debug("Logout");
            vars.clientUid = null;
            _sendMsgTo(clientWindow, {logout: true});
        }
    }

    function _stopServerCheck() {
        clearTimeout(timerServerCheck);
    }

    function _serverOnMessage(e) {
        var instructions = _getJson(e.data),
            clientUid = instructions.clientUid,
            setOptions = instructions.setOptions;
        if (setOptions) {
            sso.setOptions(setOptions);
        }
        _debug("Handle in iframe");
        if (clientUid) {
            _debug("Aware client uid: " + clientUid);
            vars.clientUid = clientUid;
        }
        if (instructions.check) {
            _serverCheck();
        }
        if (instructions.stopCheck) {
            _debug("Stop checking");
            _stopServerCheck();
        }
    }

    if (!JSON || !fetch || !localStorage) {
        throw new Error("Please include `JSON`, `fetch` and `localStorage` polyfill scripts to your page.");
    }
    _listen(w, "load", function () {
        sso.init(w.$ssoOptions);
    });
}(window, document);
