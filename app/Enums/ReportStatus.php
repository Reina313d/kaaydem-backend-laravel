<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Ouvert = 'ouvert';
    case EnCoursTraitement = 'en_cours_traitement';
    case Resolu = 'resolu';
    case Rejete = 'rejete';
}
