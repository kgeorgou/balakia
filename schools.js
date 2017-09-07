var map;

/* **********************************************************************************************
 * displaySchools: 	Εμφανίζει σε πίνακα τα σχολεία (Δημοτικά, Γυμνάσια ή Λύκεια) 
 * 					του Δήμου που έχει επιλεγεί. 
 * 					Καλείται όταν τον κουμπί 'Εμφάνιση σχολείων' στο schools.html πατηθεί 
 * Input: 			
 * Output: 			Τα σχολεία του δήμου που έχει επιλεγεί. 
 *********************************************************************************************** */

function displaySchools() {
	
	var element = document.getElementById("viewschsDimoi");
	var dimos = element.options[element.selectedIndex].value;
	
	
	var element = document.getElementById("viewschsType");
	var type = element.options[element.selectedIndex].value;
	
		
	if(dimos=="empty" && type=="empty") {
		alert("Παρακαλώ επιλέξτε δήμο και τύπο σχολείου.");
	}
	else if(dimos=="empty"){
		alert("Παρακαλώ επιλέξτε δήμο.");
	}
	else if(type=="empty"){
		alert("Παρακαλώ επιλέξτε τύπο σχολείου.");
	}
	else {
		
		$.ajax({
			url : 'php/readDB.php',
			type : 'POST',
			data : {"func":"readschoolsbydimostype","dimos":dimos,"type":type},
			dataType : 'json',
			error: function(error) {
					alert("Απετυχε η ανάκτηση δεδομένων από τη βάση δεδομένων.\n" + 
	        		error.statusText + ": " + error.status);
				   },
			success : function(jsonSchDetails) {
				
				// H PHP επιστρέφει λάθος
				if(jsonSchDetails.err=='true'){
					alert(jsonSchDetails.errdescr);				
				} else {
					
					var element = document.getElementById("output_container");
					while (element.firstChild) {
						element.removeChild(element.firstChild);
					}
					
					
					
					// create table
					var $table = $('<table id="schoolsTab">');
					// caption
					$table.append('<caption> </caption>')
					// thead
					.append('<thead>').children('thead')
					.append('<tr />').children('tr').append('<th>Κωδικός</th><th>Όνομα</th><th>Διεύθυνση</th><th>Γεωγρ. Πλάτος</th><th>Γεωγρ. Ύψος</th><th>Χρώμα</th>');
					//tbody
					var $tbody = $table.append('<tbody />').children('tbody');
					$.each(jsonSchDetails, function(){
						
						// add row
						$tbody.append('<tr />').children('tr:last')
						.append("<td><input id='schCode' type='text' maxlength='10' style='width: 50px;' value='"+this.code+"' disabled></td>")
						.append("<td><input id='schName' type='text' maxlength='50' style='width: 180px;' value='"+this.name+"' disabled></td>")
						.append("<td><input id='schAddress' type='text' maxlength='100' style='width: 180px;' value='"+this.address+"' disabled></td>")
						.append("<td><input id='schLat' type='text' style='width: 90px;' value='"+this.lat+"' disabled></td>")
						.append("<td><input id='schLng' type='text' style='width: 90px;' value='"+this.lng+"' disabled></td>")
						.append("<td><input id='schColor' type='color' style='width: 50px;' value='"+this.color+"' disabled></td>")
						.append("<td class='arrange_td'><input type='button' class='editButton' id='schEditButton' title='Αλλαγή'   onclick='editSchool(this, false);'/></td>")
						.append("<td class='arrange_td'><input type='button' class='saveButton' id='schSaveButton' title='Αποθήκευση' onclick='updateSchoolDetails(this);' disabled /></td>")
						.append("<td class='arrange_td'><input type='button' class='deleteButton' id='schDeleteButton' title='Διαγραφή' onclick='deleteSchool(this);' /></td>");
					});
					// add table to dom
					$table.appendTo('#output_container');
					
				}				
			}
		});			
	}
}

/***********************************************************************************************
 * addSchools: 	    Εμφανίζει την φόρμα για τη δήλωση νέου σχολείου. 
 * 					Από την ίδια φόρμα ο χρήστη πατώντας ένα κουμπί μπορεί να εισάγει τα αρχεία 
 * 					από αρχείο Excel. (to be implemented) $$$$$$  
 * 					Καλείται όταν τον κουμπί 'Νέο σχολείο' στο schools.html πατηθεί και
 * 					από την συνάρτηση saveSchoolDetails() αφού γίνει η αποθήκευση ενός σχολείου
 * 					για να ξαναεμφανιστεί η άδεια φόρμα
 * Input: 			
 * Output: 			Φόρμα με τα πεδία που πρέπει να συμπληρωθούν για το νέο σχολείο. 
 *********************************************************************************************** */
function addSchools() {
	
	var element = document.getElementById("output_container");
	while (element.firstChild) {
		element.removeChild(element.firstChild);
	}
	
	var $table = $('<table id="newSchoolTab">');
	
	var inputItem = $('<tr><th><div class="table_title" > Καταχώρηση νέου σχολείου </div></td></tr>');
	$table.append(inputItem);  
	
	/* TO BE IMPLEMENTED
	 * var inputItem = $('<tr><td><div class="fieldLabel"><br><b>Εισαγωγή σχολείων από αρχείο: </b>&nbsp &nbsp &nbsp &nbsp\
			<input class="button" type="button" id="importSchoolsButton"  onclick="importSchools()" value="Επιλογή αρχείου" /></div> </td></tr>');
	$table.append(inputItem); */
	
	var inputItem = $('<tr><td><div class="fieldLabel"></br>Κωδικός</div> <input type="text" maxlength="10" id="newschCode" name="newschCode" style="width:100px;"></td></tr>');
	$table.append(inputItem);  
	    
	var inputItem = $('<tr><td><div class="fieldLabel">Όνομα σχολείου</div> <input type="text" maxlength="50" id="newschName" name="newschName" style="width:400px;"></td></tr>');
	$table.append(inputItem);
	
	var inputItem = $('<tr><td><div class="fieldLabel">Τύπος σχολείου</div> <div> \
			<select id="newschType" name="newschType" > \
			<option value="empty"></option> \
			<option value="ΔΗΜΟΤΙΚΟ">ΔΗΜΟΤΙΚΟ</option> \
			<option value="ΓΥΜΝΑΣΙΟ">ΓΥΜΝΑΣΙΟ</option> \
			<option value="ΛΥΚΕΙΟ">ΛΥΚΕΙΟ</option> </select> \
			</div></td></tr>');
	$table.append(inputItem);
	
	var inputItem = $('<tr><td><div class="fieldLabel">Διεύθυνση σχολείου</div> <input type="text"  maxlength="100" id="newschAddress" name="newschAddress" style="width:400px;"></td></tr>');
	$table.append(inputItem);
	
	var inputItem = $('<tr><td><div class="fieldLabel">Δήμος</div>  \
			<div id="dimoidiv"> \
				<select id="newschDimoi" name="newschDimoi"> <option value="empty"></option> </select> \
			    <script> loadDimous("#newschDimoi");</script></div> </td></tr>');
	$table.append(inputItem);
	
	var inputItem = $('<tr><td><div class="fieldLabel">Χρώμα</div> <input id="newschColor" type="color" style="width:100px;"> </td></tr>');
	$table.append(inputItem); 
	var inputItem = $('<tr><td> <p class="container2 button"> \
			<input type="button" id="saveSchoolButton"  onclick=saveSchoolDetails() value="Αποθήκευση" style="width:150px;"/> \
			</p>  </td> </tr>');
	$table.append(inputItem);

	// add table to dom
	$table.appendTo('#output_container');
				
}
/***********************************************************************************************
 * editSchool: 	    Αλλάζει το status των textboxes από disabled σε enabled ή αντίστροφα.  
 * 					Καλείται όταν πατηθεί το κουμπί 'Αλλαγή' (displaySchools) ή 
 * 					'Αποθήκευση' (updateSchoolDetails) στο schools.js  
 * Input: 			obj: είναι η γραμμή του πίνακα των σχολείων όπου έχει πατηθεί Edit ή Save.	
 * 					status: (true ή false)
 * Output: 			
 *********************************************************************************************** */
function editSchool(obj, status){
	
	var row = obj.parentNode.parentNode;

	row.childNodes[1].childNodes[0].disabled = status;
	row.childNodes[2].childNodes[0].disabled = status;
	row.childNodes[5].childNodes[0].disabled = status;
	row.childNodes[7].childNodes[0].disabled = status;
}


/***********************************************************************************************
 * saveSchoolDetails:	Αποθηκεύει στη βάση δεδομένων ένα νέο σχολείο. Διαβάζει την διεύθυνση του σχολείου
 * 						που δίνει ο χρήστης και με τη βοήθεια του geocoding υπολογίζει τις συντεταγμένες.  
 * 						Καλείται όταν πατηθεί το κουμπί 'Αποθήκευση' (συνάρτηση addSchools()  στο schools.js)  
 * Input: 				
 * Output: 				Εμφανίζει σχετικό μήνυμα για την αποθήκευση δεδομένων 
 *********************************************************************************************** */
function saveSchoolDetails(){
	
	
	var school = new Array();
	
	//code
	school[0] = document.getElementById("newschCode").value;
	
	//name
	school[1] = document.getElementById("newschName").value;
	
	//address
	school[2] = document.getElementById("newschAddress").value;
	
	//color
	school[3] = document.getElementById("newschColor").value;
	
	//type
	school[4] = document.getElementById("newschType").value;
		
	//dimos_code
	dimos = document.getElementById("newschDimoi");
	school[5] = dimos.options[dimos.selectedIndex].value;
	school[6] = dimos.options[dimos.selectedIndex].innerHTML;

	schAddress = school[2]; 
	
	geocoder = new google.maps.Geocoder();
    
    map = new google.maps.Map(document.getElementById('map'));
    
 
    setTimeout(function() {
    	
    	geocoder.geocode({ 'address': schAddress }, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
            	
            	var location = results[0].geometry.location;

                var latitude = parseFloat(location.lat().toFixed(8)),
                    longitude = parseFloat(location.lng().toFixed(8));
                
                school[7] = latitude;
                school[8] = longitude;
                
                $.ajax({
            		url : 'php/writeDB.php',
            		type : 'POST',
            		data : {"func":"saveschooldetails","school":school},
            		dataType : 'json',
            		error: function(error) {
    						alert("Απετυχε η ενημέρωση της  βάσης δεδομένων.\n" + 
    			        		error.statusText + ": " + error.status);
    					},
            		success : function() {
            				alert("Τα δεδομένα αποθηκεύτηκαν.");
            				
            				// Εμφανίζει ξανά την άδεια φόρμα για τη δήλωση νέου σχολείου. 
            				addSchools();
            			}
                });
            	
            } else {
                alert("Απέτυχε ο προσδιορισμός των συντεταγμένων του σχολείου. Παρακαλώ εισάγετε έγκυρη διεύθυνση. Error:" + status);
            }
        });
    	
    	}, 500);
}

/***********************************************************************************************
 * updateSchoolDetails:	Αποθηκεύει στη βάση δεδομένων τις αλλαγές που έγιναν για το σχολείο.
 * 						Βρίσκει τις συντεταγμένες σε περίπτωση που η διεύθυνση του σχολείου έχει αλλάξει.   
 * 						Καλείται όταν πατηθεί το κουμπί 'Αποθήκευση' στο schools.js (displaySchools)  
 * Input: 				obj: είναι η γραμμή του πίνακα των σχολείων που έγινε η Αποθήκευση
 * Output: 				Εμφανίζει σχετικό μήνυμα για την αποθήκευση δεδομένων 
 *********************************************************************************************** */
function updateSchoolDetails(obj){
	
	var row = obj.parentNode.parentNode;

	var school = new Array();
	
	//code
	school[0] = row.childNodes[0].childNodes[0].value;
	//name
	school[1] = row.childNodes[1].childNodes[0].value;
	//address
	school[2] = row.childNodes[2].childNodes[0].value;
	//color
	school[3] = row.childNodes[5].childNodes[0].value;
	
	schAddress = school[2]; 
	
	geocoder = new google.maps.Geocoder();

    map = new google.maps.Map(document.getElementById('map'));

 
    setTimeout(function() {
    	
    	geocoder.geocode({ 'address': schAddress }, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
            	
            	var location = results[0].geometry.location;

                var latitude = parseFloat(location.lat().toFixed(8)),
                    longitude = parseFloat(location.lng().toFixed(8));
              
                school[4] = latitude;
                school[5] = longitude;
                $.ajax({
            		url : 'php/writeDB.php',
            		type : 'POST',
            		data : {"func":"updateschooldetails","school":school},
            		dataType : 'json',
            		error: function(error) {
    					alert("Απετυχε η ενημέρωση της  βάσης δεδομένων.\n" + 
    			        		error.statusText + ": " + error.status);
    						   },
    				success: function() {
    						// Εμφανίζει τις νέες συντεταγμένες στα αντίστοιχα πλαίσια. 
               				row.childNodes[3].childNodes[0].value=latitude;
            				row.childNodes[4].childNodes[0].value=longitude;
            				
            				// Αλλάζει την κατάσταση των πεδίων από editable se non-editable
            				editSchool(obj, true);
            				alert("Τα δεδομένα αποθηκεύτηκαν.");
    						}
                });
            	
            } else {
                alert("Απέτυχε ο προσδιορισμός των συντεταγμένων του σχολείου. Παρακαλώ εισάγετε έγκυρη διεύθυνση: " + status);
            }
        });
    	
    	}, 500);
}

/***********************************************************************************************
 * deleteSchool:	Διαγράφει, δηλαδή ορίζει το status=Deleted για ένα συγκεκριμένο σχολείο. 
 * 					Καλείται όταν πατηθεί το κουμπί 'Διαγραφή' στο schools.js (displaySchools)  
 * Input: 			obj: είναι η γραμμή του πίνακα των σχολείων που πατήθηκε η Διαγραφή
 * Output: 			Εμφανίζει σχετικό μήνυμα για την διαγραφή του σχολείου.  
 *********************************************************************************************** */
function deleteSchool(obj){
	
	if (confirm("Είστε βέβαιοι ότι θέλετε να διαγράψετε αυτό το σχολείο;") == true) {
		
		var row = obj.parentNode.parentNode;

		var school = new Array();
		
		//code
		school[0] = row.childNodes[0].childNodes[0].value;

		$.ajax({
			url : 'php/writeDB.php',
			type : 'POST',
			data : {"func":"deleteschool","school":school},
			dataType : 'json',
			error: function(error) {
					alert("Απετυχε η διαγραφή του σχολείου από τη βάση δεδομένων.\n" + 
		        		error.statusText + ": " + error.status);
					   },
			success : function(result) {		
					alert("Το σχολείο διαγράφτηκε.")
					// Εμφανίζει ξανά τα σχολεία χωρίς τι διαγραμμένο σχολείο. 
					displaySchools();
			}
	    });
	} 	            
            	
}

function importSchools() {
	
	
}
    
         