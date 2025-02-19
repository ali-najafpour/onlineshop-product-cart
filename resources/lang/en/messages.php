<?php
return [
    'success_content' => 'Your request done successfully',
    'success_title' => 'Success',
    'error_title' => 'Error',
    'error_content' => 'Something is wrong...',
    'file_stored' => 'File stored successfully',
    'error_app_section_limit' => "Sorry, you can't save more than :count feathers for this app section",

    'not_verified' => [
        'email' => "Your email has not been verified.",
        'cell_number' => 'Your phone number has not been verified.',
        'both' => 'Your phone number or email has not been verified.',
    ],
    'credentials' => "Username or password is wrong, please try again.",
    'user_dosnt_exists' => "this user dos'nt exist",
    'logout' => "You have successfully logged out!",
    'password_changed' => "Your password was changed successfully.",
    'verify_code_sent' => [
        'email' => "A verification code was sent to your email.",
        'phone' => 'A verification code was sent to your phone number.',
    ],
    'invalid_code' => "The entered code is invalid.",
    'remember_code_sent' => [
        'email' => "A verification code was sent to your email.",
        'phone' => 'A verification code was sent to your phone number.',
    ],
    'not_admin' => "Sorry, you don't have permission to log in to this page.",
    'contest_is_active' => "Operation Not Allowed; the contest is active.",
    'stage_is_active' => "Operation Not Allowed; the stage is active.",
    'group_is_active' => "Operation Not Allowed; the stage is active.",
    'group_is_published' => "Operation Not Allowed; the group is published.",
    'song_is_published' => "Operation Not Allowed; the song is already published.",
    'user_id_not_match_post_id' => "Conflict; the post does not belong to this user.",
    'user_id_not_match_story_id' => "Conflict; the story does not belong to this user.",
    'stage_id_not_match_contest_id' => "Conflict; the stage does not belong to this contest.",
    'group_id_not_match_stage_id' => "Conflict; the group does not belong to this stage.",
    'contest_not_found' => "Contest is not published.",
    'user_is_not_a_participant' => 'Forbidden; this user is not a participant.',
    'candidate_is_a_participant' => 'Operation Not Allowed; this candidate is a participant.',
    'max_stages_limit' => 'Operation Not Allowed; the contest has its maximum number of stages.',
    'max_participants_limit' => 'Operation Not Allowed; the group has its maximum number of participants.',
    'min_participants_required' => 'Operation Not Allowed; the group has less than two participants.',
    'contest_voting_inactive' => 'Operation Not Allowed; voting is not allowed for this contest.',
    'no_published_stage_found' => 'Not Found; no published stage found in this contest.',
    'published_video_exists' => 'Conflict; this participant has another published video.',
    'unpublished_group_exists' => 'Operation Not Allowed; this stage has unpublished group(s).',
    'active_contest_should_be_published' => 'Conflict; the active contest should be published.',
    'end_date_is_passed' => 'Conflict; the end_date is passed. If you want to activate the contest you should change its end_date.',
    'participant_exists' => 'Conflict; a participant of this user exists in this contest.',
    'participant_with_no_published_video_exists' => 'Operation Not Allowed; at least one participant has no published video.',
    'active_stage_should_be_published' => 'Conflict; the active stage should be published.',
    'end_date_is_passed' => 'Conflict; the end_date is passed. If you want to activate you should change its end_date.',

    'notifications' => [
        'product' => [
            "store" => "New product added."
        ]
    ],
];
