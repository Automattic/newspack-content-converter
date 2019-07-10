/*
 * NCC interface.
 */
window.onload = function() {

	/*
	 * Hide the Block Editor for performance reasons, and to be replaced with custom interface.
	 */
	document.getElementsByClassName("edit-post-header")[0].style.display = "none";
	document.getElementsByClassName("edit-post-layout__content")[0].style.display = "none";
	document.getElementsByClassName("edit-post-sidebar")[0].style.display = "none";

	/*
	 * Custom interface insertion, HTML and script resources.
	 * @var converterScriptResources - localized data, array of script resources
	 */
	document.getElementsByClassName("edit-post-header")[0].insertAdjacentHTML(
		'afterend',
		'<div id="root"></div>'
	);
	converterScriptResources.forEach( function( source ) {
		var jsScript = document.createElement( "script" );
		jsScript.type = "text/javascript";
		jsScript.src = source;
		jsScript.async = true;
		document.getElementsByTagName( "head" )[0].appendChild(jsScript);
	} );

};
