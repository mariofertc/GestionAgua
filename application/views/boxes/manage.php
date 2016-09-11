<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
$(document).ready(function()
{

    init_table_sorting();
    enable_select_all();
    enable_checkboxes();
    enable_row_selection();
    enable_search('<?php echo site_url("$controller_name/suggest")?>','<?php echo $this->lang->line("common_confirm_search")?>');
    //enable_delete('<?php echo $this->lang->line($controller_name."_confirm_delete")?>','<?php echo $this->lang->line($controller_name."_none_selected")?>');
    //enable_bulk_edit('<?php echo $this->lang->line($controller_name."_none_selected")?>');

});

function init_table_sorting()
{
	//Only init if there is more than one row
	if($('.tablesorter tbody tr').length >1)
	{
		$("#sortable_table").tablesorter(
		{
			sortList: [[1,1]],
			headers:
			{
				0: { sorter: false},
				4: { sorter: false}
			}

		});
	}
}

function post_box_form_submit(response)
{
	if(!response.success)
	{
		set_feedback(response.message,'error_message',true);
	}
	else
	{
		//set_feedback(response.message,'success_message',false);
		//set_feedback(response.box_id,'success_message',false);
		//return;
		//This is an update, just update one row
		if(jQuery.inArray(response.box_id,get_visible_checkbox_ids()) != -1)
		{
		    //set_feedback("Listo U",'success_message',false);
			update_row(response.box_id,'<?php echo site_url("$controller_name/get_row")?>');
			set_feedback(response.message,'success_message',false);
			//set_feedback("Listo",'success_message',true);

		}
		else //refresh entire table
		{
		   //set_feedback("Listo I",'success_message',false);
			do_search(true,function()
			{
				//highlight new row
				hightlight_row(response.box_id);
				set_feedback(response.message,'success_message',false);
			});
		}
		
	}
}

</script>
<div id="title_bar">
	<div id="title" class="float_left"><?php echo $this->lang->line('common_list_of').' '.$this->lang->line('module_'.$controller_name); ?></div>
	<div id="new_button">
		<?php echo anchor("$controller_name/view/-1?width=$form_width",
		"<div class='big_button' style='float: left;'><span>".$this->lang->line($controller_name.'_new')."</span></div>",
		array('class'=>'thickbox none','title'=>$this->lang->line($controller_name.'_new')));
		?>
	</div>
	
</div>
<div id="table_action_header">
	<ul>
		<li class="float_right">
		<img src='<?php echo base_url()?>images/spinner_small.gif' alt='spinner' id='spinner' />
		<?php echo form_open("$controller_name/search",array('id'=>'search_form')); ?>
		<input type="text" name ='search' id='search'/>
		</form>
		</li>
	</ul>
</div>
<div id="table_holder">
<?php echo $manage_table; ?>
</div>
<div id="feedback_bar"></div>
<div id="feedback_bar"></div>
<?php $this->load->view("partial/footer"); ?>