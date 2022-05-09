function emailIsValid (email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
}

function passwordIsValid (password) {
    return /^([a-zA-Z0-9_-]){4,20}$/.test(password)
}

function Keygen(){
    var tokens = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
    chars = 5,
    segments = 4,
    keyString = "";
    for( var i = 0; i < segments; i++ ) {
        var segment = "";

        for( var j = 0; j < chars; j++ ) {
           var k = Math.floor( Math.random() * 35 );
            segment += tokens[ k ];
        }

        keyString += segment;

        if( i < ( segments - 1 ) ) {
            keyString += "-";
        }
    }
    $('#purchase_code').val('PROWEBBER-' + keyString);
}

function verify_purchase_code() {
    $('#checkBtn').html('Calling server...');
    var purchase_code = $.trim($('#purchase_code').val());
    if (purchase_code != '') {
        setTimeout(function(){
            // Succes
            $('#checkTitle').html('Create Database');
            $('#domaincheck').css('display', 'none');
            $('#errText').html('<span style="color:green;font-size:13px;">Awesome! you purchase code has been accepted.</span>');
            $('#errText').show();
            document.cookie = "chkJS=yes;";
            $('#installerform').show(500);
        }, 2000);
    } else {
        $('#errText').html('<span style="color:red;">You missed out details.</span>');
        $('#errText').show();
        return false;
    }

}

	$(document).on('submit','.theme_btn',function(){
			var admin_email = $.trim($('input[name="admin_email"]').val());
			var admin_password = $.trim($('input[name="admin_password"]').val());
			var db_host = $.trim($('input[name="db_host"]').val());
			var db_name = $.trim($('input[name="db_name"]').val());
			var db_uname = $.trim($('input[name="db_uname"]').val());
			var db_pwd = $.trim($('input[name="db_pwd"]').val());
			if(admin_email =="" || !emailIsValid (admin_email)){
				$('#errText').html('<span style="color:red;">Administrator E-mail field can not be empty.</span>');
				return false;
			}
			if(admin_password =="" || !passwordIsValid (admin_password)){
				$('#errText').html('<span style="color:red;">Administrator password field can not be empty and it must be between 4 and 20 chars.</span>');
				return false;
			}
			if(db_host ==""){
				$('#errText').html('<span style="color:red;">Hostname field can not be empty.</span>');
				return false;
			}
			if(db_name ==""){
				$('#errText').html('<span style="color:red;">Database field can not be empty.</span>');
				return false;
			}
			if(db_uname ==""){
				$('#errText').html('<span style="color:red;">Username field can not be empty.</span>');
				return false;
			}
			
			$( ".theme_btn" ).submit();
		
	});