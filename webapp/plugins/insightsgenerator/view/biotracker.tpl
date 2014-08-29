{foreach from=$i->related_data.changes item=change name=changed }
<div class="biotracker_change">
    {include file=$tpl_path|cat:"_user.tpl" user=$change.user bio_before=$change.before bio_after=$change.after}
</div>
{/foreach}

