<!--SM:include file="Common_Object_Header.tpl":SM-->
    <div data-role="content">
        <ul data-role="listview" id="theobject" data-theme="d" data-divider-theme="d">
            <!--SM:foreach $renderPage as $object:SM-->
                <div id="Talk_<!--SM:$object.intTalkID:SM-->">
                    <script type="text/Javascript">
                        $(function() {
                            $('#Talk_<!--SM:$object.intTalkID:SM--> .progressive_basic').hide();
                            $('#Talk_<!--SM:$object.intTalkID:SM--> .readwrite').hide();
                            $('#editmode_<!--SM:$object.intTalkID:SM-->').change(function(){
                                if ($(this).val() === '1') {
                                    $('#Talk_<!--SM:$object.intTalkID:SM--> .haseditable').hide();
                                    $('#Talk_<!--SM:$object.intTalkID:SM--> .readwrite').show();
                                } else {
                                    $('#Talk_<!--SM:$object.intTalkID:SM--> .readwrite').hide();
                                    $('#Talk_<!--SM:$object.intTalkID:SM--> .haseditable').show();
                                }
                            });
                        });
                    </script>
                    <!--SM:if isset($object.isEditable) && count($object.isEditable) > 0:SM-->
                        <div>
                            <label for="editmode_<!--SM:$object.intTalkID:SM-->">Edit mode
                                <select name="editmode_<!--SM:$object.intTalkID:SM-->" id="editmode_<!--SM:$object.intTalkID:SM-->" data-role="slider">
                                    <option value="0" selected>off</option>
                                    <option value="1">on</option>
                                </select>
                            </label>
                            <a href="<!--SM:$SiteConfig.baseurl:SM-->talk/<!--SM:$object.intTalkID:SM-->?HTTPaction=delete" data-role="button" data-inline="true" data-icon="delete">Delete</a>
                        </div>
                                
                        <form action="<!--SM:$SiteConfig.baseurl:SM-->talk/<!--SM:$object.intTalkID:SM-->" method="post">
                    <!--SM:/if:SM--><!-- This is editable - add the form tags -->

                    <div id="intTalkID">
                        <div>
                            <span class="progressive_basic">Read only: </span>
                            <label>
                                Talk ID:
                                <data>
                                    <!--SM:$object.intTalkID:SM-->
                                </data>
                            </label>
                        </div>
                    </div>

                    <!--SM:include file="Elements/TextBox.tpl" field='strTalk' label=$object.labels.strTalk edit=$object.isEditable.strTalk current=$object.strTalk:SM-->
                    <!--SM:include file="Elements/TextBox.tpl" field='strTalkSummary' label=$object.labels.strTalkSummary edit=$object.isEditable.strTalkSummary current=$object.strTalkSummary:SM-->
                    <!--SM:include file="Elements/Boolean.tpl" field='hasNsfwMaterial' label=$object.labels.hasNsfwMaterial edit=$object.isEditable.hasNsfwMaterial current=$object.hasNsfwMaterial:SM-->
                    <!--SM:include file="Elements/Boolean.tpl" field='isLocked' label=$object.labels.isLocked edit=$object.isEditable.isLocked current=$object.isLocked:SM-->
                    <!--SM:include file="Elements/SingleElementDropDown.tpl" field='intRoomID' label=$object.labels.intRoomID edit=$object.isEditable.intRoomID current=$object.arrRoom.current:SM-->
                    <!--SM:include file="Elements/SingleElementDropDown.tpl" field='intSlotID' label=$object.labels.intSlotID edit=$object.isEditable.intSlotID current=$object.arrSlot.current:SM-->
                    <!--SM:include file="Elements/TextBox.tpl" field='intLength' label=$object.labels.intLength edit=$object.isEditable.intLength current=$object.intLength:SM-->
                    <!--SM:include file="Elements/MultiElementFromTextBox.tpl" field='jsonLinks' label=$object.labels.jsonLinks edit=$object.isEditable.jsonLinks list=$object.arrLinks:SM-->
                    <!--SM:include file="Elements/SingleElementDropDown.tpl" field='intUserID' label=$object.labels.intUserID edit=$object.isEditable.intUserID current=$object.arrUser.current:SM-->
                    <!--SM:include file="Elements/MultiElementFromArray.tpl" field='jsonOtherPresenters' label=$object.labels.jsonOtherPresenters edit=$object.isEditable.jsonOtherPresenters list=$object.arrPresenters exclude=$object.intUserID:SM-->
                    <!--SM:include file="Elements/AssociatedRecords.tpl" field="arrAttendee" label=$object.labels.arrAttendee list=$object.arrAttendee:SM-->

                    <!--SM:if isset($Object_User.current) && $Object_User.current != null && $Object_User.current != false:SM-->
                        <div class="readonly haseditable">
                            <!--SM:if $object.isPresenting:SM-->
                                <a href="<!--SM:$SiteConfig.thisurl:SM-->" data-role="button" data-inline="true" data-icon="star-blue">Presenting</a>
                            <!--SM:elseif $object.isAttending != false:SM-->
                                <a href="<!--SM:$SiteConfig.baseurl:SM-->attendee/<!--SM:$object.isAttending:SM-->?HTTPaction=delete" data-role="button" data-inline="true" data-icon="star-gold">Decline</a>
                            <!--SM:else:SM-->
                                <a href="<!--SM:$SiteConfig.baseurl:SM-->attendee/new?intTalkID=<!--SM:$object.intTalkID:SM-->" data-role="button" data-inline="true" data-icon="star-grey">Attend</a>
                            <!--SM:/if:SM-->
                        </div>
                    <!--SM:/if:SM-->
                    <!--SM:if isset($object.isEditable) && count($object.isEditable) > 0:SM-->
                        <div class="readwrite"><input type="submit" value="Update"/></div>
                        </form>
                    <!--SM:/if:SM--><!-- This is editable - add the form tags -->
                </div>
                <!--SM:if !$object@last:SM--><hr /><!--SM:/if:SM-->
            <!--SM:/foreach:SM-->
        </ul>
    </div>
<!--SM:include file="Common_Object_Footer.tpl":SM-->