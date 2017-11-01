//GLOBAL NAMESPACE

var WFBNS = {};
var HitsOnly = 0;
//END
function loadMainChart(ChartName)
	{
	var obj; 
	var derp;

	var ctx = document.getElementById(ChartName);

var data = {
    labels: [
	    "Verified by Bayesian Analysis",
        "Verified by 100% Blast Match",
        "Blast Match awaiting Verification",		
        "Unclassified Samples"
			],
    datasets: [
        {
            data: [0, 0, 0, 0],
            backgroundColor: [
                "#88BBD6",
                "#99D3DF",
                "#E9E9E9",				
                "#a5a5a5"
            ],
            hoverBackgroundColor: [
                "#88BBD6",
                "#99D3DF",
                "#E9E9E9",				
                "#a5a5a5"
            ],
			label: [
                "1",
                "2",
                "3",
				"4"
            ]
        }
		]

		};

var options = {
	segmentShowStroke : false,
	animateScale: true
				};

var myChart = new Chart(ctx, {
    type:"doughnut",
    data: data,
    options: options
	});

$.post("ops.php", {command: "DBStats"},
	function(datas)
		{	
		obj = JSON.parse(datas);
		data.datasets[0].data = [obj.bayes, obj.blast, obj.verify, obj.unclass];
		myChart.update();


var legend = document.createElement("DIV");	
	
var n = data.datasets[0].data.length;		
var x = 0;		
while(x < n)
	{
var sublegend = document.createElement("DIV");
var text = document.createTextNode(data.datasets[0].data[x] + " " + data.labels[x]);
sublegend.setAttribute("width", "200px");
sublegend.appendChild(text);

sublegend.style.backgroundColor = data.datasets[0].backgroundColor[x];
sublegend.style.width = "300px";
legend.appendChild(sublegend);
	document.getElementById('canvasp').appendChild(legend);
	x = x + 1;
	}



});
	}		

function load_alignmentview(altype, bindpoint)
	{
	var seconddiv = document.getElementById(bindpoint);
	var viewerdiv = document.createElement("div");
	var firstchild = seconddiv.firstChild;
	seconddiv.insertBefore(viewerdiv, firstchild);
	viewerdiv.id = "viewerdiv";
	var alignmenttype = altype;
	var dataset = altype;
//GenerateFasta
	$.post("ops.php", {command: 'load_align', dataset: dataset},
	function(data)
		{	
		var seqs = msa.io.fasta.parse(data);
		var m = msa({
			 el: viewerdiv,
			 seqs: seqs,
			 conf: {hasRef: true},
			 vis: {},
			zoomer: {labelNameLength: 250, alignmentHeight: 300, rowHeight: 15},
			colorscheme: {opacity: 0.7}
		});
		
		m.render();	
		m.g.colorscheme.addStaticScheme("own",{A: "red", C: "blue", G: "yellow", T: "green"});	
		m.g.colorscheme.set("scheme", "own");
		m.g.zoomer.setLeftOffset(740);
		
		if(altype === 'import_review_align')
		{
		m.g.zoomer.set("alignmentHeight", 450);
		m.g.on("row:click", function(data){
		var sequenceid = seqs[data.seqId].name;
		loadAlignControl(sequenceid);
		WFBNS.LoadAlignControl_seqid = sequenceid;
		
				});
		}
		else
		{
		m.g.on("row:click", function(data){

		//alert(seqs[data.seqId].name);
		var table = $('#maintable').DataTable();
		table.search(seqs[data.seqId].name);
		table.draw();
									});
		}					
								
									
									
		});

	}
		
		
function loadRegister()
	{
	$.post("ops.php", {command: "Load_Register"},
	function(data)
		{	
		document.getElementById('secondcontent').innerHTML = data;
		register_validation();

		});

		
		}

function register_validation()
	{
	function check_all_valid()
		{
		if(fname_valid == 1 && sname_valid == 1 && email_valid == 1 && pwd_valid == 1)
			{
			document.getElementById("register_button").disabled = false;
			return true;
			}
			else
				{
				document.getElementById("register_button").disabled = true;
				return false;
				}
		}
		
	var fname_valid = 0;
	var sname_valid = 0;
	var email_valid = 0;
	var pwd_valid = 0;
	
	var fname_box = document.getElementById("fname");
	var sname_box = document.getElementById("sname");	
	var email_box = document.getElementById("email");	
	var pwd_box = document.getElementById("pwd");	
	
	fname_box.addEventListener("input", function (event)
	{
	document.getElementById("fname_validator").classList = "";
	if(fname_box.validity.valid)
		{
		document.getElementById("fname_validator").innerHTML = " &#10004;";
		document.getElementById("fname_validator").classList.add("check_green");
		fname_valid = 1;
		}
		else
			{
			fname_valid = 0;
			document.getElementById("fname_validator").innerHTML = " This cannot be blank";
			document.getElementById("fname_validator").classList.add("cross_red");
			}
	check_all_valid();
	}, false);
	
	
	sname_box.addEventListener("input", function (event)
	{
	document.getElementById("sname_validator").classList = "";	
	if(sname_box.validity.valid)
		{
		sname_valid = 1;
		document.getElementById("sname_validator").innerHTML = " &#10004;";
		document.getElementById("sname_validator").classList.add("check_green");		
		}
		else
			{
			sname_valid = 0;
			document.getElementById("sname_validator").innerHTML = " This cannot be blank";
			document.getElementById("sname_validator").classList.add("cross_red");
			}
			check_all_valid();
	}, false);

	email_box.addEventListener("input", function (event)
	{
	document.getElementById("email_validator").classList = "";	
	if(email_box.validity.valid)
		{
		email_valid = 1;	
		document.getElementById("email_validator").innerHTML = " &#10004;";
		document.getElementById("email_validator").classList.add("check_green");		
		}
		else
			{
			email_valid = 0;
			document.getElementById("email_validator").innerHTML = " Enter a valid Email";
			document.getElementById("email_validator").classList.add("cross_red");
			}
			check_all_valid();
	}, false);

	
	pwd_box.addEventListener("input", function (event)
	{
	document.getElementById("password_validator").classList = "";	
	if(pwd_box.validity.valid)
		{
		pwd_valid = 1;	
		document.getElementById("password_validator").innerHTML = " &#10004;";
		document.getElementById("password_validator").classList.add("check_green");
		}
		else
			{
			pwd_valid = 0;	
			document.getElementById("password_validator").innerHTML = " This cannot be blank";
			document.getElementById("password_validator").classList.add("cross_red");
			}
			check_all_valid();
	}, false);	
	
		
	$('.register_enter_listener').keypress(function(e)
	{
		if(e.keyCode == 13)
			{
			if(check_all_valid())
				{
				alert('Hi');
				}		
			}
			});	
	
	}
		
function createuser()
	{

	var fname = document.getElementById("fname").value;
	var sname = document.getElementById("sname").value;
	var email = document.getElementById("email").value;
	var pwd = document.getElementById("pwd").value;			

	
		
	$.post("ops.php", {command: "NewUser", fname: fname, sname: sname, email: email, pwd: pwd} ,
	function(data)
		{
		document.getElementById('secondcontent').innerHTML = data;
		
		})
		}		

function loadSequences()
	{
	document.getElementById('secondcontent').innerHTML = 'Loading Data, Please wait....';
	
	$.post("ops.php", {command: "LoadSequences"},
	function(data)
		{	
		document.getElementById('secondcontent').innerHTML = data;
		$('#maintable').DataTable( 
				{
			paging: false,
			colReorder: true
				}
			);
		});
		

	}
	
function loadLists()
	{
	document.getElementById('secondcontent').innerHTML = '';
	
	$.post("ops.php", {command: "LoadLists"},
	function(data)
		{	
		document.getElementById('secondcontent').innerHTML = data;
		var list = document.getElementById("listbox");
		var newlistvalue = list.value;
		list.addEventListener("change", function(){LoadListContents(list)});
		});
		

		
	}	

function dellist(listid, confirmed)
	{
	//alert(listid);
	var maindiv = document.getElementById("secondcontent");
	if(confirmed == 0)
		{
	var delbutton = document.getElementById("listdelbutton");
	delbutton.innerHTML = "Click to Confirm";
	delbutton.setAttribute("class", "button alert");
	delbutton.setAttribute("onclick", "dellist(" + listid + "," + 1 + ")");
		}
		if(confirmed == 1)
			{
			$.post("ops.php", {command: "DelList", listid: listid},
			function(data)
			{
			maindiv.innerHTML = data + "<br>Redirecting to list management...";	
			setTimeout(loadLists,3000);
			});	
			
			
			}
	}
	
function del_p_seq(seqid, confirmed)
	{
	//alert(listid);
	var maindiv = document.getElementById("secondcontent");
	if(confirmed == 0)
		{
	var delbutton = document.getElementById(seqid);
	delbutton.innerHTML = "Click to Confirm";
	delbutton.setAttribute("class", "button alert");
	delbutton.setAttribute("onclick", "del_p_seq(" + seqid + "," + 1 + ")");
		}
		if(confirmed == 1)
			{
			$.post("ops.php", {command: "DelPSequence", seqid: seqid},
			function(data)
			{
						
			});	
			var rowid = 'row-' + seqid;	
			var row = document.getElementById(rowid);
			//row.parentNode.removeChild(row);
			var table = $('#maintable').DataTable();
			table.row(row).remove().draw();
			
			}
		}

function del_all_seq(confirmed)
	{
	var maindiv = document.getElementById("secondcontent");
	if(confirmed == 0)
		{
	var delbutton = document.getElementById('global_delete');
	alert('Clicking the Global Delete button will remove ALL user sequences');
	delbutton.innerHTML = "Click to Confirm";
	delbutton.setAttribute("class", "button alert");
	delbutton.setAttribute("onclick", "del_all_seq(1)");
		}
		if(confirmed == 1)
			{
			$.post("ops.php", {command: "DelAllPSequence"},
			function(data)
			{
						
			});	
			loadMySequences();
			}
	}
	
function newlist()
	{
	var tablediv = document.getElementById('tablediv');
	document.getElementById("listnewbuttondiv").innerHTML = '';
	tablediv.innerHTML = '<div class="row"><div class="large-5 columns end"><form name="listform" action=""><label for="inputlistname" id="listnamelabel">List Name: <input type="text" id="inputlistname"></input></div></div><div class="row"><div class="large-5 columns"></label><label>List Description: <input type="text" id="inputlistdescription"></input></label></form></div></div><div class="row"><div class="large-2 large-offset-6 end"><button type="button" class="button" onclick="newlistcreate();">Submit</button></div>';
	}	
	
function newlistcreate()
	{
	var maindiv = document.getElementById("secondcontent");
	var listname = document.getElementById("inputlistname").value;
	var listdesc = document.getElementById("inputlistdescription").value;
	maindiv.innerHTML = "Creating new List";
	$.post("ops.php", {command: "NewList", listname: listname, listdesc:listdesc},
	function(data)
		{
		maindiv.innerHTML = data;	
		setTimeout(loadLists,5000);
		});	
		
	}
	
function LoadListContents(list)
	{
	document.getElementById("tablediv").innerHTML = 'Loading List... ';
	
	var listtitle = list[list.selectedIndex].label;	
	$.post("ops.php", {command: "LoadListsContent", listid: list.value, listtitle: listtitle},
	function(data)
		{
		document.getElementById("tablediv").innerHTML = '';	
		var selectlistdiv = document.getElementById("listdelbuttondiv");
		var deletebutton = "<button type='button' id='listdelbutton' class='button warning' onclick='dellist(" + list.value + ","+ 0 +");'>Delete List</button>";
		selectlistdiv.innerHTML = deletebutton;
		//alert(data);	
		var parseddata = JSON.parse(data);
		var first = parseddata.header;
		//alert(first);
		var tablediv = document.getElementById("tablediv");
		var mttable = document.createElement("TABLE");
		tablediv.appendChild(mttable);
		var header = mttable.createTHead();
		var row = header.insertRow(-1);
		//Create Headers
		for (var i in parseddata.header) 
		{
		var cell = row.insertCell(-1);
		cell.innerHTML = parseddata.header[i];
		};	
		//Create Share button
		var cell = row.insertCell(-1);
		cell.innerHTML = "Share";
		//Create Delete Button
		var cell = row.insertCell(-1);
		cell.innerHTML = "Remove";		
		
		for (var i in parseddata.data) 
		{
		var newrow = mttable.insertRow(-1);
			for (var j in parseddata.header) 
				{
				var newcell = newrow.insertCell(-1);
				if(parseddata.header[j] == "P(AB)")
					{
					if(parseddata.data[i][parseddata.header[j]] < 0.0001)
						{
						newcell.style.color = "red";
						}
					}
				newcell.innerHTML = parseddata.data[i][parseddata.header[j]];
				
				};
		//Create Share button
		var newcell = newrow.insertCell(-1);
		newcell.innerHTML = "<button class='button success' type='button' disabled>Share</button>";
		//Create Delete Button
		var newcell = newrow.insertCell(-1);
		newcell.innerHTML = "<button class='button alert' id=" + parseddata.data[i][parseddata.header[0]] + " type='button' onclick='removefromlist(" + parseddata.data[i][parseddata.header[0]] + ");'>Remove</button>";		
		};			
		load_alignmentview('list_align', 'tablediv');
		});

	}	

function addtoactivelist(idtoadd)
	{

	document.getElementById(idtoadd).setAttribute("class", "button warning");
	
	$.post("ops.php", {command: "AddToList", itemid: idtoadd},
	function(data)
		{
		document.getElementById(idtoadd).setAttribute("class", "button success");
		});	
	
	}
	
function removefromlist(idtoremove)
	{

	document.getElementById(idtoremove).setAttribute("class", "button warning");
	
	$.post("ops.php", {command: "RemoveFromList", itemid: idtoremove},
	function(data)
		{
		document.getElementById(idtoremove).setAttribute("class", "button success");
		});	
	
	}	

function hitsonlychange(changeto)
	{
	HitsOnly = changeto;
	loadMySequences();
	
	}
	
function loadMySequences()
	{
	document.getElementById('secondcontent').innerHTML = 'Loading Data....';
	$.post("ops.php", {command: "LoadMySequences", hitsonly: HitsOnly},
	function(data)
		{	
		document.getElementById('secondcontent').innerHTML = data;
		$('#maintable').DataTable( 
				{
			paging: false,
			colReorder: true
				});

			var filterdiv = document.getElementById("maintable_filter");
			var btn = document.createElement("BUTTON");
			btn.setAttribute("class", "button");
			t = document.createTextNode("Clear Search");
			btn.addEventListener("click", function(){
				
			var table = $('#maintable').DataTable();
			table.search("");
			table.draw();			
						});
			btn.appendChild(t);
			filterdiv.appendChild(btn);		
				
		//load_alignmentview('priv_align', 'secondcontent');
		});
		
	}
	
function loadMySidebar()
	{
	document.getElementById('data-reveal').innerHTML = 'Loading Data, Please wait....';
	
	$.post("ops.php", {command: "LoadMySidebar"},
	function(data)
		{	
		document.getElementById('data-reveal').innerHTML = data;
		
		});
	}	
	
	function load_ms_sidebar(entryid)
		{
	document.getElementById('data-reveal').innerHTML = 'Loading';
	
	$.post("ops.php", {command: "LoadMSSidebar", Seq: entryid},
	function(data)
		{	
		document.getElementById('data-reveal').innerHTML = data;
		});		
		}

function loadGenbankXML()
	{
	document.getElementById('secondcontent').innerHTML = 'Loading Data, Please wait....';
	
	$.post("ops.php", {command: "Genbank_Import"},
	function(data)
		{	
		document.getElementById('secondcontent').innerHTML = data;
		});
	}

function blastAssay()
	{
	document.getElementById('secondcontent').innerHTML = 'Loading Data, Please wait....';
	
	$.post("ops.php", {command: "Blast_Assay"},
	function(data)
		{	
		document.getElementById('secondcontent').innerHTML = data;
		});
	}	

// Start login page code	
function loadLogin()
	{
	document.getElementById('secondcontent').innerHTML = 'Loading Data, Please wait....';
	
	$.post("ops.php", {command: "loadLogin"},
	function(data)
		{	
		document.getElementById('secondcontent').innerHTML = data;
			
	$('.login_enter_listener').keypress(function(e)
	{
		if(e.keyCode == 13)
			{
	submit_function();
				}
				});
		});

	}
	
function login()
	{
	document.getElementById("result").innerHTML = "Attempting Login";	
	var email = document.getElementById("email").value;
	var pwd = document.getElementById("pwd").value;			
		
		
	$.post("ops.php", {lcommand: "Login", email: email, pwd: pwd},
	function(data)
		{
		document.getElementById("result").innerHTML = data;	
		});

		}

function loadNoobNavigation()
	{
	var content = '<div class="large-6 large-offset-3 columns" align="center"><button type="button" class="button large" onclick="upload_sequences();">UPLOAD FASTA</button> <button type="button" class="button large secondary" onclick="loadMySequences();">VIEW RESULTS</button></div>';	
		
	document.getElementById('secondcontent').innerHTML = content;
	}
		
		
function submit_function()
		{
	
	var email = document.getElementById("email").value;
	var pwd = document.getElementById("pwd").value;
	
	$.post("ops.php", {command: "Login", email: email, pwd: pwd},
	function(data)
		{	
		if(data == 1)
		{
		document.getElementById('result').innerHTML = "POTATO!";
		document.getElementById('secondcontent').innerHTML = "POTATO!";
		location.reload();
		}
		else
			{
			document.getElementById('result').innerHTML = "We were unable to log you in. Please try again.";
			}
		});
		
		}
		

// End login page code

// Sequencing set

function upload_sequences()
		{
		$.post("ops.php", {command: "Upload_Sequences"},
	function(data)
		{	
		document.getElementById('secondcontent').innerHTML = data;

		});
		
		}
		

		
		
function upload_sequence_now()
			{
				//alert("Beginning Upload");
				
				var uploadbutton = document.getElementById("upload_button");
				uploadbutton.setAttribute("class", "large button warning");
				uploadbutton.innerHTML = 'Uploading...';
				
				var fileinfo = document.getElementById("upload_file").files;
				var file = document.getElementById("upload_file").files;
				
				var form = document.getElementById("data");
				
				var formData = new FormData(form);
				formData.append("command", "Upload_New_Sequence");

				var request = new XMLHttpRequest();
				request.onreadystatechange = function() {
								if(request.readyState == 4)
								{
								document.getElementById("secondcontent").innerHTML = request.responseText;
								blast_sequences();
								}
								//alert(request.statusText);
														}
				request.open("POST", "ops.php");
				request.send(formData);	
				
			}
		
function blast_sequences()
		{

			
		document.getElementById('secondcontent').innerHTML = 'Loading Data, Please Wait...';
	
	$.post("ops.php", {command: "Blast_Sequences"},
	function(data)
		{	
		document.getElementById('secondcontent').innerHTML = data;
		//alert(data);
		loadMySequences();
		});
		
		}	
		
function statistics()
	{
		document.getElementById('secondcontent').innerHTML = 'Loading Data, Please Wait...';
	
	$.post("ops.php", {command: "Statistics_Main"},
	function(data)
		{	
		document.getElementById('secondcontent').innerHTML = data;

		});		
			
	}	

function export_alignments(confirmed)
	{
		document.getElementById('secondcontent').innerHTML = 'Loading Data, Please Wait...';
	
	$.post("ops.php", {command: "export_all_alignments", conf: confirmed},
	function(data)
		{	
		document.getElementById('secondcontent').innerHTML = data;

		});		
			
	}	
	
function stats_create_alignment()
	{

		var stats_log = document.getElementById('stats_result');		
		stats_log.innerHTML = stats_log.innerHTML + "<br>" + "Creating Alignment..";

	$.post("ops.php", {command: "Create_Alignment"},
	function(data)
		{	
		var stats_log = document.getElementById('stats_result');	
		stats_log.innerHTML = stats_log.innerHTML + "<br>" + data;
		send_alignment();
		});		
			
	}	
		
function send_alignment()
	{
	var stats_log = document.getElementById('stats_result');		
		stats_log.innerHTML = stats_log.innerHTML + "<br>" + "Sending Alignment to Processing Server";
		$.post("ops.php", {command: "Send_Alignment"},
	function(data)
		{	
		var stats_log = document.getElementById('stats_result');	
		stats_log.innerHTML = stats_log.innerHTML + "<br>" + data;
		});	
	
	
	}
	
function load_sandbox()
	{
	var seconddiv = document.getElementById('secondcontent');	
		
	$.post("ops.php", {command: "read_nexus_data"},
	function(data)
		{	
		seconddiv.innerHTML = data;
		});
	}

function load_alignmenteditor()
	{
	var seconddiv = document.getElementById('secondcontent');
	
	seconddiv.innerHTML = '<h2> Alignment Editor </h2>';

	var newdiv = document.createElement("div");
	newdiv.setAttribute("id", "sandbox");
	seconddiv.appendChild(newdiv);
	
	load_alignmentview('import_review_align', 'sandbox');
		
	var mgdiv = document.createElement("div");
	mgdiv.setAttribute("id", "mgdiv");
	seconddiv.appendChild(mgdiv);	
	}
	
function loadAlignControl(sequenceid)
	{
	var controldiv = document.getElementById("mgdiv");
	controldiv.innerHTML = sequenceid;
	
	$.post("ops.php", {command: "Load_Sequence_Info", genbank: sequenceid},
	function(data)
		{	
		controldiv.innerHTML = data;
		WFBNS.LoadAlignControl_seqmoveamount = parseInt(0);
		});
		
			window.onkeyup = function(e) {
			   var key = e.keyCode ? e.keyCode : e.which;

			   if (key == 13) {
				   update_reload();
								}
										}	
	}
	
function move_sequence(mod)
	{
	move_amount = WFBNS.LoadAlignControl_seqmoveamount;
	WFBNS.LoadAlignControl_seqmoveamount = move_amount + parseInt(mod);
	//alert(WFBNS.LoadAlignControl_seqmoveamount);
	document.getElementById("move_amount").innerHTML = WFBNS.LoadAlignControl_seqmoveamount;
	}	
	
function update_reload()
	{
	update_button = document.getElementById("update_button");
	update_button.disabled = true;
		
	$.post("ops.php", {command: "Move_Sequence", sequenceid: WFBNS.LoadAlignControl_seqid, shift: WFBNS.LoadAlignControl_seqmoveamount},	
	function(data)
		{	
		document.getElementById("control").innerHTML = data;		
		});	
	}