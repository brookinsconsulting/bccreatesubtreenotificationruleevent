{let $classes=fetch('class', 'list')}

<script type="text/javascript">
<!--
    var optionArray;
    var valueArray;

    optionArray = new Array( );
    valueArray = new Array( );

    {foreach $classes as $class}
        optionArray['{$class.identifier}'] = new Array( );
        valueArray['{$class.identifier}'] = new Array( );
        {let $attributes = fetch( 'class', 'attribute_list', hash( 'class_id', $class.id ) )}
        {foreach $attributes as $attribute}
            optionArray['{$class.identifier}'].push( "{$attribute.name}" );
            valueArray['{$class.identifier}'].push( "{$attribute.id}" );
        {/foreach}
        {/let}
    {/foreach}

    {literal}
function syncSelectBoxes( fromid, toid, optionArray, valueArray )
{
    var i;
    var from;
    var to;
    var fromlen;
    var selected;
    var selectedValue;
    var optionlen;
    var chosenOptions;
    var chosenValues;
    var tolen;

    from = document.getElementById( fromid );
    to = document.getElementById( toid );

    if ( from != null && to != null )
    {
        removeAllOptions( toid );
        if ( from.selectedIndex > -1 )
        {
            selected = from.options[from.selectedIndex];

            selectedValue = selected.value;

            chosenOptions = optionArray[selectedValue];
            chosenValues = valueArray[selectedValue];
            
            optionlen = chosenOptions.length;
            
            for ( i = 0; i < optionlen; i++ )
            {
                tolen = to.length;
                to.options[tolen] = new Option( chosenOptions[i], chosenValues[i], false, false );
            }
        }
    }
}

function removeAllOptions( selectid )
{
    var i;
    var select;

    select = document.getElementById( selectid );

    if ( select != null )
    {
        for ( i = ( select.length - 1 ); i >= 0; i-- )
        {
            select.options[i] = null;
        }
    }
}

    {/literal}
-->
</script>

<div class="block">
<fieldset>
<legend>{'Attributes with related user objects'|i18n( 'design/admin/workflow/eventtype/edit' )}</legend>

<select id="ClassSelection_{$event.id}" onchange="javascript:syncSelectBoxes( 'ClassSelection_{$event.id}', 'AttributeSelection_{$event.id}', optionArray, valueArray );">
    {foreach $classes as $class}
        <option value="{$class.identifier}">{$class.name|wash}</option>
    {/foreach}
</select>

<select name="AttributeSelection_{$event.id}" id="AttributeSelection_{$event.id}">
    <option value="">&nbsp;</option>
</select>

<input type="submit" class="button" name="CustomActionButton[{$event.id}_AddAttribute]" value="{'Add attribute'|i18n( 'design/admin/workflow/eventtype/edit' )}" />

<script type="text/javascript">
<!--
    javascript:syncSelectBoxes( 'ClassSelection_{$event.id}', 'AttributeSelection_{$event.id}', optionArray, valueArray );
-->
</script>

{if $event.selected_attributes|count|gt(0)}
<table class="list" cellspacing="0">
<thead>
<tr>
<th class="tight">&nbsp;</th>
<th>{'Attributes'|i18n( 'design/admin/workflow/eventtype/edit' )}</th>
</tr>
</thead>
<tbody>
{foreach $event.selected_attributes as $attributeID}
{def $attribute=fetch( 'content', 'class_attribute', hash( 'attribute_id', $attributeID ) )}
{if $attribute}
{def $class=fetch( 'content', 'class', hash( 'class_id', $attribute.contentclass_id ) )}
<tr>
<td><input type="checkbox" name="DeleteAttributeIDArray_{$event.id}[]" value="{$attributeID}" /></td>
<td>{$class.name|wash} / {$attribute.name|wash}</td>
</tr>
{undef $class}
{/if}
{undef $attribute}
{/foreach}
</tbody>
</table>
<input type="submit" class="button" name="CustomActionButton[{$event.id}_RemoveAttributes]" value="{'Remove selected'|i18n( 'design/admin/workflow/eventtype/edit' )}" />
{/if}
</fieldset>
</div>