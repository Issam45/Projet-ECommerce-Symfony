<?php
namespace App\Core;

class Notification {

    private $tag;
    private $contenu;
    private $couleur;

    public function __construct($tag, $contenu, $couleur = NotificationCouleur::PRIMARY)
    {
        $this->tag = $tag;
        $this->contenu = $contenu;
        $this->couleur = $couleur;
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function getContenu()
    {
        return $this->contenu;
    }

    public function getCouleur()
    {
        return $this->couleur;
    }
}

abstract class NotificationCouleur {
    public const PRIMARY = "alert-primary";
    public const SECONDARY = "alert-secondary";
    public const SUCCESS = "alert-success";
    public const DANGER = "alert-danger";
    public const WARNING = "alert-warning";
    public const INFO = "alert-info";
    public const LIGHT = "alert-light";
    public const DARK = "alert-dark";
}