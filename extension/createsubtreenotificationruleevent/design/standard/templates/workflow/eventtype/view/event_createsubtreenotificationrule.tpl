<div class="element">
{"Attributes with related user objects"|i18n("design/standard/workflow/eventtype/view")}: 
{def $selectedAttributeIDList=$event.selected_attributes}
{foreach $selectedAttributeIDList as $selectedAttributeID}
{delimiter}, {/delimiter}
{def $selectedAttribute=fetch('content','class_attribute',hash('attribute_id', $selectedAttributeID ))}
{if $selectedAttribute}
{def $class=fetch( 'content', 'class', hash( 'class_id', $selectedAttribute.contentclass_id ) )}
<a href={concat("/class/view/",$class.id)|ezurl}>{$class.name|wash}</a> / {$selectedAttribute.name|wash}
{undef $class}
{/if}
{undef $selectedAttribute}
{/foreach}
{undef $selectedAttributeIDList}
</div>