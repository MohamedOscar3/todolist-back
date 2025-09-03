<?php

namespace App\Enums;

/**
 * Enum for task stages in the workflow
 */
enum TaskStages: string
{
    case BACKLOG = 'backlog';
    case IN_PROGRESS = 'in_progress';
    case REVIEW = 'review';
    case DONE = 'done';
}
