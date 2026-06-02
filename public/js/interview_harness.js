/**
 * Interview test harness — runs candidate JavaScript against a set of test
 * cases inside a sandboxed iframe (no same-origin, no parent access), so
 * candidate code cannot touch the page, cookies, or the network.
 *
 * window.runInterviewTests(code, entry, cases) -> Promise<Array<result>>
 *   code   : the candidate's full source (defines the entry function)
 *   entry  : the function name to invoke (trusted, from the server)
 *   cases  : [{ args: [...], expected: any, sample?: bool }]
 *   result : { pass, got, expected, args, error?, timeout? }
 *
 * Used by the candidate (sample cases only) and by admin review (full suite).
 */
(function () {
    window.runInterviewTests = function (code, entry, cases) {
        return new Promise(function (resolve) {
            var id = 'h' + Math.random().toString(36).slice(2);
            // Neutralise any attempt to break out of the script context.
            var safeCode = String(code).replace(/<\/(script)/gi, '<\\/$1');
            var payload = JSON.stringify(cases || []);
            var entryName = String(entry).replace(/[^A-Za-z0-9_$]/g, ''); // identifier only

            var html =
                '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body><script>' +
                '(function(){' +
                'function __eq(a,b){try{return JSON.stringify(a)===JSON.stringify(b);}catch(e){return false;}}' +
                'var __cases=' + payload + ';' +
                'var __out=[];' +
                'try{' +
                safeCode + '\n' +
                'for(var __i=0;__i<__cases.length;__i++){' +
                '  var __c=__cases[__i];' +
                '  try{' +
                '    if(typeof ' + entryName + ' !== "function"){throw new Error("Function ' + entryName + ' is not defined");}' +
                '    var __g=' + entryName + '.apply(null,__c.args);' +
                '    __out.push({pass:__eq(__g,__c.expected),got:__g,expected:__c.expected,args:__c.args,sample:!!__c.sample});' +
                '  }catch(__e){__out.push({pass:false,error:String(__e&&__e.message||__e),expected:__c.expected,args:__c.args,sample:!!__c.sample});}' +
                '}' +
                '}catch(__fatal){__out=[{pass:false,fatal:String(__fatal&&__fatal.message||__fatal)}];}' +
                'parent.postMessage({__interviewId:"' + id + '",results:__out},"*");' +
                '})();' +
                '<\/script></body></html>';

            var iframe = document.createElement('iframe');
            iframe.setAttribute('sandbox', 'allow-scripts');
            iframe.style.display = 'none';

            var done = false;
            function finish(results) {
                if (done) return;
                done = true;
                window.removeEventListener('message', onMsg);
                try { iframe.parentNode && iframe.parentNode.removeChild(iframe); } catch (e) {}
                resolve(results);
            }
            function onMsg(ev) {
                if (ev.data && ev.data.__interviewId === id) finish(ev.data.results || []);
            }
            window.addEventListener('message', onMsg);

            // Guard against infinite loops / hangs in candidate code.
            setTimeout(function () {
                finish([{ pass: false, timeout: true, error: 'Your code timed out (possible infinite loop).' }]);
            }, 5000);

            iframe.srcdoc = html;
            document.body.appendChild(iframe);
        });
    };
})();
