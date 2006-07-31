<?php

/*
Copyright (C) 2006 SCK-CEN
Written by Kristof Coomans ( http://blog.kristofcoomans.be )

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

include_once( 'kernel/classes/ezworkflowtype.php' );

define( 'EZ_WORKFLOW_TYPE_SCKCREATESUBTREENOTIFICATIONRULE', 'sckcreatesubtreenotificationrule' );

class SckCreateSubtreeNotificationRuleType extends eZWorkflowEventType
{
    function SckCreateSubtreeNotificationRuleType()
    {
        $this->eZWorkflowEventType( EZ_WORKFLOW_TYPE_SCKCREATESUBTREENOTIFICATIONRULE, ezi18n( 'kernel/workflow/event', 'SCK-CEN Create Subtree Notification Rule' ) );
        // limit workflows which use this event to be used only on the post-publish trigger
        $this->setTriggerTypes( array( 'content' => array( 'publish' => array( 'after' ) ) ) );
    }

    function &attributeDecoder( &$event, $attr )
    {
        $retValue = null;
        switch( $attr )
        {
            case 'selected_attributes':
            {
                $implodedAttributeList = $event->attribute( 'data_text1' );

                $attributeList = array();
                if ( $implodedAttributeList != '' )
                {
                    $attributeList = explode( ';', $implodedAttributeList );
                }
                return $attributeList;
            }

            default:
            {
                eZDebug::writeNotice( 'unknown attribute:' . $attr, 'SckCreateSubtreeNotificationRuleType' );
            }
        }
        return $retValue;
    }

    function typeFunctionalAttributes()
    {
        return array( 'selected_attributes' );
    }

    function fetchHTTPInput( &$http, $base, &$event )
    {
    }

        /*
     \reimp
    */
    function customWorkflowEventHTTPAction( &$http, $action, &$workflowEvent )
    {
        $eventID = $workflowEvent->attribute( 'id' );
        $module =& $GLOBALS['eZRequestedModule'];

        switch ( $action )
        {
            case 'AddAttribute':
            {
                if ( $http->hasPostVariable( 'AttributeSelection_' . $eventID ) )
                {
                    $attributeID = $http->postVariable( 'AttributeSelection_' . $eventID );
                    $workflowEvent->setAttribute( 'data_text1', implode( ';', array_unique( array_merge( $this->attributeDecoder( $workflowEvent, 'selected_attributes' ), array( $attributeID ) ) ) ) );
                }
            } break;

            case 'RemoveAttributes':
            {
                if ( $http->hasPostVariable( 'DeleteAttributeIDArray_' . $eventID ) )
                {
                    $deleteList = $http->postVariable( 'DeleteAttributeIDArray_' . $eventID );
                    $currentList = $this->attributeDecoder( $workflowEvent, 'selected_attributes' );
                    
                    if ( is_array( $deleteList ) )
                    {
                        $dif = array_diff( $currentList, $deleteList );
                        $workflowEvent->setAttribute( 'data_text1', implode( ';', $dif ) );
                    }
                }
            } break;

            default:
            {
                eZDebug::writeNotice( 'unknown custom action: ' . $action, 'SckCreateSubtreeNotificationRuleType' );
            }
        }
    }

    function execute( &$process, &$event )
    {
        $parameters = $process->attribute( 'parameter_list' );
        eZDebug::writeDebug( $parameters, 'SckCreateSubtreeNotificationRuleType::execute process parameter_list' );

        include_once( 'kernel/classes/ezcontentobject.php' );
        $object =& eZContentObject::fetch( $parameters['object_id'] );

        $datamap = $object->attribute( 'data_map' );
        $attributeIDList = $event->attribute( 'selected_attributes' );

        foreach ( $datamap as $attribute )
        {
            if ( in_array( $attribute->attribute('contentclassattribute_id'), $attributeIDList ) )
            {
                eZDebug::writeDebug( 'found matching attribute: ' . $attribute->attribute('contentclassattribute_id'), 'SckCreateSubtreeNotificationRuleType' );

                // get related objects
                $relatedObjects =& $object->relatedContentObjectList( false, false, $attribute->attribute('contentclassattribute_id') );

                include_once( 'kernel/classes/datatypes/ezuser/ezuser.php' );
                foreach( $relatedObjects as $relatedObject )
                {
                    // check if the related object is a user
                    $userID = $relatedObject->attribute( 'id' );
                    $relatedUser = eZUser::fetch( $userID );

                    if ( $relatedUser )
                    {
                        // set notification rule
                        include_once( 'kernel/classes/notification/handler/ezsubtree/ezsubtreenotificationrule.php' );

                        $nodeIDList =& eZSubtreeNotificationRule::fetchNodesForUserID( $userID, false );
                        $mainNodeID = $object->attribute( 'main_node_id' );
                        if ( !in_array( $mainNodeID, $nodeIDList ) )
                        {
                            $rule =& eZSubtreeNotificationRule::create( $mainNodeID, $userID );
                            $rule->store();
                            eZDebug::writeDebug( 'Set a notification for ' . $relatedObject->attribute( 'name' ) );
                        }
                    }
                }
            }
        }

        return EZ_WORKFLOW_TYPE_STATUS_ACCEPTED;
    }
}

eZWorkflowEventType::registerType( EZ_WORKFLOW_TYPE_SCKCREATESUBTREENOTIFICATIONRULE, 'sckcreatesubtreenotificationruletype' );

?>
