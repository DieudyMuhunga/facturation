/**
 * js/scanner.js
 * Scanner de code-barres par webcam — API ZXing 0.19.2 stable.
 *
 * Inclure dans la page APRES le CDN ZXing :
 *   <script src="https://unpkg.com/@zxing/library@0.19.2/umd/index.min.js"></script>
 *   <script src="../../js/scanner.js"></script>
 *
 * Usage minimal :
 *   Scanner.init('video-scanner', 'code_barre');
 *
 * Options completes :
 *   Scanner.init('video-scanner', 'code_barre', {
 *     btnDemarrer  : 'btn-scanner',       // id bouton start (defaut)
 *     btnArreter   : 'btn-stop-scanner',  // id bouton stop  (defaut)
 *     selectCamera : 'select-camera',     // id <select> cameras (optionnel)
 *     autoSubmit   : true,                // soumettre le form apres scan
 *     onDetect     : function(code) {}    // callback custom
 *   });
 */

var Scanner = (function () {

    'use strict';

    // etat interne 
    var _reader   = null;   // ZXing.BrowserMultiFormatReader
    var _stream   = null;   // MediaStream actif
    var _actif    = false;
    var _videoId  = '';
    var _inputId  = '';
    var _opts     = {};

    //  Verifier que ZXing 0.19.x est bien charge 

    function _ok() {
        if (typeof ZXing === 'undefined' || !ZXing.BrowserMultiFormatReader) {
            _erreur('ZXing non charge. Verifiez le CDN dans la page HTML.');
            return false;
        }
        return true;
    }

    //  Lister les cameras via l'API navigateur standard  

    function listerCameras(selectId) {
        if (!selectId) { return; }
        var sel = document.getElementById(selectId);
        if (!sel) { return; }

        if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
            sel.innerHTML = '<option value="">Camera non supportee</option>';
            return;
        }

        navigator.mediaDevices.enumerateDevices()
            .then(function (devices) {
                var cameras = devices.filter(function (d) {
                    return d.kind === 'videoinput';
                });

                sel.innerHTML = '';

                if (cameras.length === 0) {
                    sel.innerHTML = '<option value="">Aucune camera</option>';
                    return;
                }

                cameras.forEach(function (cam, i) {
                    var opt   = document.createElement('option');
                    opt.value = cam.deviceId;
                    opt.text  = cam.label || ('Camera ' + (i + 1));
                    // Selectionner la camera arriere par defaut sur mobile
                    if (/back|rear|environment/i.test(cam.label)) {
                        opt.selected = true;
                    }
                    sel.appendChild(opt);
                });
            })
            .catch(function (e) {
                console.warn('[Scanner] enumerateDevices :', e);
                sel.innerHTML = '<option value="">Erreur cameras</option>';
            });
    }

    
    // Demarrer le scan 

    function demarrer(videoId, inputId, options) {
        videoId = videoId || _videoId;
        inputId = inputId || _inputId;
        options = options || _opts;

        _videoId = videoId;
        _inputId = inputId;
        _opts    = options;

        if (!_ok()) { return; }

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            _erreur('getUserMedia non supporte par ce navigateur.');
            return;
        }

        var videoEl = document.getElementById(videoId);
        if (!videoEl) {
            console.error('[Scanner] <video id="' + videoId + '"> introuvable.');
            return;
        }

        /* arreter un scan precedent proprement */
        if (_actif) { arreter(); }

        _actif = true;
        videoEl.style.display = 'block';
        _majBoutons(true);

        /* choisir le deviceId */
        var deviceId = options.deviceId || null;
        if (!deviceId && options.selectCamera) {
            var sel = document.getElementById(options.selectCamera);
            deviceId = sel ? (sel.value || null) : null;
        }

        /* contraintes camera */
        var contraintes = {
            video: deviceId
                ? { deviceId: { exact: deviceId } }
                : { facingMode: { ideal: 'environment' } },
            audio: false
        };

        // demander acces camera 
        navigator.mediaDevices.getUserMedia(contraintes)
            .then(function (stream) {
                _stream      = stream;
                videoEl.srcObject = stream;
                return videoEl.play();
            })
            .then(function () {
                /* demarrer la lecture ZXing sur l'element video */
                _reader = new ZXing.BrowserMultiFormatReader();

                /* hints optionnels : tous formats courants */
                var hints = new Map();
                hints.set(ZXing.DecodeHintType.POSSIBLE_FORMATS, [
                    ZXing.BarcodeFormat.QR_CODE,
                    ZXing.BarcodeFormat.EAN_13,
                    ZXing.BarcodeFormat.EAN_8,
                    ZXing.BarcodeFormat.CODE_128,
                    ZXing.BarcodeFormat.CODE_39,
                    ZXing.BarcodeFormat.UPC_A,
                    ZXing.BarcodeFormat.UPC_E,
                    ZXing.BarcodeFormat.DATA_MATRIX,
                    ZXing.BarcodeFormat.ITF
                ]);
                _reader.setHints(hints);

                _reader.decodeFromVideoDevice(deviceId, videoEl, function (result, err) {
                    if (result) {
                        _onResultat(result.getText());
                    } else if (err && !(err instanceof ZXing.NotFoundException)) {
                        _erreur('Erreur scanner : ' + (err.message || err.name || err));
                    }
                });
            })
            .catch(function (err) {
                if (!_actif) { return; } /* arrete volontairement, ignorer */

                if (err && err.name === 'NotAllowedError') {
                    _erreur('Permission camera refusee. Autorisez l\'acces dans votre navigateur.');
                } else if (err && err.name === 'NotFoundError') {
                    _erreur('Aucune camera trouvee sur cet appareil.');
                } else if (err && err instanceof ZXing.NotFoundException) {
                    /* aucun code detecte dans le delai — normal, on continue */
                } else {
                    _erreur('Erreur camera : ' + (err ? (err.message || err.name || err) : 'inconnue'));
                    console.error('[Scanner]', err);
                }
            });
    }

    // Traiter un code detecte
    
    function _onResultat(code) {
        /* remplir le champ */
        var input = document.getElementById(_inputId);
        if (input) {
            input.value = code;
            input.dispatchEvent(new Event('input',  { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // feedback visuel 
        var videoEl = document.getElementById(_videoId);
        _flash(videoEl);

        // callback custom ou comportement par defaut 
        if (typeof _opts.onDetect === 'function') {
            _opts.onDetect(code);
        } else {
            arreter();
            if (input && _opts.autoSubmit !== false) {
                var form = (typeof input.closest === 'function') ? input.closest('form') : null;
                if (!form && input.form) {
                    form = input.form;
                }
                if (form) {
                    setTimeout(function () { form.submit(); }, 250);
                }
            }
        }
    }

    
    //   Arreter le scanner et liberer la camera   
    
    function arreter() {
        _actif = false;

        // stopper ZXing 
        if (_reader) {
            try { _reader.reset(); } catch (e) { /* ignore */ }
            _reader = null;
        }

        // liberer le flux camera (eteint le voyant camera)
        if (_stream) {
            _stream.getTracks().forEach(function (t) { t.stop(); });
            _stream = null;
        }

        /* cacher la video */
        var videoEl = document.getElementById(_videoId);
        if (videoEl) {
            videoEl.srcObject     = null;
            videoEl.style.display = 'none';
        }

        _majBoutons(false);
    }

    //  Basculer start / stop
    
    function basculer(videoId, inputId, options) {
        _actif ? arreter() : demarrer(videoId, inputId, options);
    }

    //  init() — lier les boutons et preparer la page 
    
    function init(videoId, inputId, options) {
        options  = options  || {};
        _videoId = videoId;
        _inputId = inputId;
        _opts    = options;

        var idBtnD = options.btnDemarrer  || 'btn-scanner';
        var idBtnA = options.btnArreter   || 'btn-stop-scanner';
        var idSel  = options.selectCamera || null;

        // remplir le select cameras 
        if (idSel) {
            /* demander d'abord les permissions pour avoir les labels */
            navigator.mediaDevices && navigator.mediaDevices.getUserMedia({ video: true })
                .then(function (s) {
                    s.getTracks().forEach(function (t) { t.stop(); }); /* liberer immediatement */
                    listerCameras(idSel);
                })
                .catch(function () {
                    listerCameras(idSel); /* essayer quand meme sans labels */
                });
        }

        // bouton demarrer 
        var btnD = document.getElementById(idBtnD);
        if (btnD) {
            btnD.addEventListener('click', function (e) {
                e.preventDefault();
                var devId = idSel
                    ? ((document.getElementById(idSel) || {}).value || null)
                    : null;
                demarrer(videoId, inputId, Object.assign({}, options, { deviceId: devId }));
            });
        }

        // bouton arreter 
        var btnA = document.getElementById(idBtnA);
        if (btnA) {
            btnA.addEventListener('click', function (e) {
                e.preventDefault();
                arreter();
            });
        }

        // cacher la video au chargement 
        var videoEl = document.getElementById(videoId);
        if (videoEl) { videoEl.style.display = 'none'; }

        _majBoutons(false);
    }

    
    // Helpers prives
    

    function _majBoutons(on) {
        var d = document.getElementById('btn-scanner');
        var a = document.getElementById('btn-stop-scanner');
        if (d) { d.style.display = on ? 'none'         : 'inline-block'; }
        if (a) { a.style.display = on ? 'inline-block' : 'none'; }
    }

    function _flash(videoEl) {
        if (!videoEl) { return; }
        var b = videoEl.style.border, o = videoEl.style.outline;
        videoEl.style.border  = '3px solid #16a34a';
        videoEl.style.outline = '4px solid rgba(22,163,74,.5)';
        setTimeout(function () {
            videoEl.style.border  = b;
            videoEl.style.outline = o;
        }, 700);
    }

    function _erreur(msg) {
        console.error('[Scanner]', msg);
        var old = document.getElementById('scanner-erreur');
        if (old) { old.remove(); }
        var div = document.createElement('div');
        div.id  = 'scanner-erreur';
        div.textContent = '⚠️ ' + msg;
        div.style.cssText = 'background:#fee2e2;color:#b91c1c;padding:10px 14px;border-radius:7px;font-size:13px;margin-top:8px;border-left:3px solid #b91c1c';
        var v = document.getElementById(_videoId);
        if (v && v.parentNode) {
            v.parentNode.insertBefore(div, v.nextSibling);
        } else {
            document.body.appendChild(div);
        }
        setTimeout(function () { if (div.parentNode) { div.remove(); } }, 6000);
    }

    
    //  API publique  
    
    return {
        init          : init,
        demarrer      : demarrer,
        arreter       : arreter,
        basculer      : basculer,
        listerCameras : listerCameras,
        estActif      : function () { return _actif; }
    };

}());
