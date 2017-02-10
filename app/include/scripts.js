jQuery.preloadImages = function() 
{ 
	for(var i = 0; i<arguments.length; i++) 
	{ 
		jQuery("<img>").attr("src", arguments[i]); 
	} 
}
$.preloadImages("images/loading.gif");

function showPanel(e) {
	var scrOfX = 0, scrOfY = 0;
	if( typeof( window.pageYOffset ) == 'number' ) 
	{
		//Netscape compliant
		scrOfY = window.pageYOffset;
		scrOfX = window.pageXOffset;
	}
	else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) 
	{
		//DOM compliant
		scrOfY = document.body.scrollTop;
		scrOfX = document.body.scrollLeft;
	} 
	else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) 
	{
		//IE6 standards compliant mode
		scrOfY = document.documentElement.scrollTop;
		scrOfX = document.documentElement.scrollLeft;
	}
	$('#login-panel').css({"left": (e.clientX-400+scrOfX) + "px", "top": (e.clientY+10+scrOfY) + "px"} );
	$('#login-panel').fadeIn("fast");
	$('#login-panel #name').focus();
	return false;
}

function showLoading() {
	$('#loadingbox').show();
	window.scrollTo(0,0);
}

function hideLoading() {
	$('#loadingbox').hide();
}

/* Show and hide Objects */
function showObj(id) {
	$('#'+id).fadeIn('fast');
}

function hideObj(id) {
	$('#'+id).hide();
}

function showhideObj(id) {
	$('#'+id).toggle(400);
}

/* http://www.kryogenix.org/code/browser/searchhi/ */
function highlightWord(node,word) {
	// Iterate into this nodes childNodes
	if (node.hasChildNodes) {
		var hi_cn;
		for (hi_cn=0;hi_cn<node.childNodes.length;hi_cn++) {
			highlightWord(node.childNodes[hi_cn],word);
		}
	}
	// And do this node itself
	if (node.nodeType == 3) { // text node
		tempNodeVal = node.nodeValue.toLowerCase();
		tempWordVal = word.toLowerCase();
		if (tempNodeVal.indexOf(tempWordVal) != -1) {
			pn = node.parentNode;
			klasse = "searchword"+w; // different colors for differnt searchterms

			if (pn.className != klasse) {
				// word has not already been highlighted!
				nv = node.nodeValue;
				ni = tempNodeVal.indexOf(tempWordVal);
				// Create a load of replacement nodes
				before = document.createTextNode(nv.substr(0,ni));
				docWordVal = nv.substr(ni,word.length);
				after = document.createTextNode(nv.substr(ni+word.length));
				hiwordtext = document.createTextNode(docWordVal);
				hiword = document.createElement("em"); // modified from span to em
				hiword.className = klasse;
				hiword.appendChild(hiwordtext);
				pn.insertBefore(before,node);
				pn.insertBefore(hiword,node);
				pn.insertBefore(after,node);
				pn.removeChild(node);
			}
		}
	}
}

function Highlight(wordstring) {
	var words = wordstring.split(" ");
	for (w=0;w<words.length;w++) {
		if (words[w].length >= 3) {
		    highlightWord(document.getElementsByTagName("body")[0],words[w]);
		}
      }
}

function showChars() 
{
	var content = $('#text').val();
	var chars = max_chars - content.length;
	if (chars < 0) 
	{
		$('#text').val(content.substring(0, max_chars));
		chars = 0;
	}
	$('#char_viewer').text(chars);
}