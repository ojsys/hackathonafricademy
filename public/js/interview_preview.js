/**
 * Live preview + requirement checklist for applied (project) interview tasks.
 * The candidate writes an HTML fragment (markup + inline <style>/<script>); we
 * wrap it in a full document and render it same-origin (via a blob URL) so its
 * fetch() calls to our endpoints carry the login cookie. A small wrapper is
 * injected that auto-attaches the interview token + exercise id to those calls.
 *
 *   window.interviewBuildPreview(code, type, ctx) -> full HTML string
 *   window.interviewCheckRequirements(code, reqs) -> [{ text, met }]
 */
(function () {
    function injector(ctx) {
        var token = JSON.stringify(String(ctx && ctx.token || ''));
        var exId  = JSON.stringify(String(ctx && ctx.exerciseId || ''));
        return '<script>(function(){' +
            'if(!window.fetch)return;' +
            'var _f=window.fetch.bind(window);' +
            'var T=' + token + ',E=' + exId + ';' +
            'function tag(u){if(typeof u!=="string")return u;' +
            'if(u.indexOf("/actions/interview_sandbox.php")===-1&&u.indexOf("/actions/interview_api.php")===-1)return u;' +
            'return u+(u.indexOf("?")===-1?"?":"&")+"exercise_id="+encodeURIComponent(E)+"&t="+encodeURIComponent(T);}' +
            'window.fetch=function(input,init){init=init||{};try{' +
            'var u=(typeof input==="string")?input:(input&&input.url)||"";' +
            'if(u.indexOf("/actions/interview_sandbox.php")!==-1||u.indexOf("/actions/interview_api.php")!==-1){' +
            'var h=new Headers((init&&init.headers)||{});' +
            'h.set("X-Interview-Token",T);h.set("X-Interview-Exercise",E);' +
            'init.headers=h;init.credentials="same-origin";' +
            'if(typeof input==="string")input=tag(input);}' +
            '}catch(e){}return _f(input,init);};' +
            '})();<\/script>';
    }

    window.interviewBuildPreview = function (code, type, ctx) {
        var base = '<style>body{font-family:system-ui,sans-serif;padding:1rem;margin:0;color:#111;background:#fff}' +
                   'input,textarea,select{display:block;margin:.35rem 0;padding:.45rem;width:100%;max-width:340px;box-sizing:border-box}' +
                   'button{padding:.45rem .9rem;margin:.35rem 0;cursor:pointer}ul{padding-left:1.2rem}</style>';
        var body;
        if (type === 'css') {
            // Pure CSS submissions: apply to a small sample document.
            body = '<style>' + code + '</style>' +
                   '<div class="cards"><div class="card"><h3>Sample</h3><div class="price">$0</div><button>Go</button></div></div>';
        } else {
            body = code; // combined / html fragment (may include its own <style>/<script>)
        }
        return '<!DOCTYPE html><html><head><meta charset="utf-8">' + base + injector(ctx) +
               '</head><body>' + body + '</body></html>';
    };

    // Lightweight heuristic requirement checker (same spirit as the lessons).
    function meetsReq(code, req) {
        var lc = code.toLowerCase(), rlc = req.toLowerCase();
        var qm = req.match(/['"`]([^'"`]{2,40}?)['"`]/g);
        if (qm) for (var i = 0; i < qm.length; i++) {
            if (lc.indexOf(qm[i].slice(1, -1).toLowerCase()) !== -1) return true;
        }
        var tagRe = /\b(html|head|body|div|span|p|h[1-6]|a|img|ul|ol|li|nav|header|footer|main|section|article|form|input|button|select|textarea|table|label|script|style)\b/g;
        var tags = rlc.match(tagRe);
        if (tags) {
            var uniq = tags.filter(function (t, i, a) { return a.indexOf(t) === i; });
            if (uniq.every(function (t) { return lc.indexOf('<' + t) !== -1; })) return true;
        }
        var keys = ['display', 'flex', 'grid', '@media', 'media query', 'border', 'padding', 'margin',
            'box-shadow', 'shadow', 'border-radius', 'rounded', 'hover', 'required', 'email', 'fetch',
            'json', 'post', 'addeventlistener', 'preventdefault', 'createelement', 'queryselector',
            'innerhtml', 'textcontent', 'input event', 'submit', 'value', 'filter', 'includes',
            'remove', 'loading', 'case-insensitive', 'tolowercase'];
        for (var k = 0; k < keys.length; k++) {
            var key = keys[k];
            if (rlc.indexOf(key) !== -1) {
                var probe = key.replace(/[^a-z@-]/g, '');
                if (probe && lc.indexOf(probe) !== -1) return true;
            }
        }
        return false;
    }

    window.interviewCheckRequirements = function (code, reqs) {
        return reqs.map(function (r) { return { text: r, met: meetsReq(code, r) }; });
    };
})();
