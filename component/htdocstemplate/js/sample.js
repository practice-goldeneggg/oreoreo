// created at 2007.05.25

function trOnMouseOver(obj) {
	obj.style.background='#99CCFF';
}
function trOnMouseOut(obj) {
	obj.style.background='#FFFFFF';
}
function debug() {
	var aid = document.forms[0].a.value;
	if (aid == '') {
		alert('no action id');
	} else {
		document.forms[0].a.name = aid;
		document.forms[0].a.value = 'y';
		document.forms[0].submit();
	}
}
function debugWithSession() {
	var aid = document.forms[0].a.value;
	if (aid == '') {
		alert('no action id');
	} else {
		document.forms[0].a.name = aid;
		document.forms[0].a.value = 'y';
		document.forms[0].submit();
	}
}

// Ajax
function createXMLHTTP() {
    if(window.XMLHttpRequest) {
        return new XMLHttpRequest();
    } else if(window.ActiveXObject) {
        try {
            return new ActiveXObject("MSXML2.XMLHTTP");
        } catch (e) {
            try {
                return new ActiveXObject("Microsoft.XMLHTTP");
            } catch(e2) {
                return null;
            }
        }
    }
}
function asyncAjax(method, url, flg, callback){
    var req = createXMLHTTP();
    req.onreadystatechange = function () {
        if (req.readyState == 4) {
            callback(req);
        }
    };
    req.open(method, url, flg);
    req.send('');     
}
