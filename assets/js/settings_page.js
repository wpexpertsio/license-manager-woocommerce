document.addEventListener('DOMContentLoaded', function(event) {
	const selectUser = jQuery('select#user');

	if (selectUser) selectUser.select2();
});