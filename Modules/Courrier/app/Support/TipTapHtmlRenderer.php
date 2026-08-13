<?php

namespace Modules\Courrier\Support;

/**
 * Convertit le contenu structuré TipTap (JSON ProseMirror) en HTML, pour le
 * seul cas où un rendu serveur est réellement nécessaire : le corps figé du
 * PDF définitif généré à la signature (voir DompdfCourrierPdfGenerator).
 * L'éditeur lui-même ne fait jamais ce rendu côté serveur (TipTapEditor.jsx)
 * — ici, chaque nœud texte est échappé explicitement, et seuls les types de
 * nœuds/marques réellement produits par l'éditeur (StarterKit) sont
 * reconnus ; tout type inconnu est silencieusement ignoré plutôt que de
 * risquer d'injecter du HTML non maîtrisé dans un document officiel.
 */
class TipTapHtmlRenderer
{
    public function render(?array $document): string
    {
        if (! $document) {
            return '';
        }

        return $this->renderNodes($document['content'] ?? []);
    }

    private function renderNodes(array $nodes): string
    {
        $html = '';

        foreach ($nodes as $node) {
            $html .= $this->renderNode($node);
        }

        return $html;
    }

    private function renderNode(array $node): string
    {
        $type = $node['type'] ?? null;
        $contenuEnfants = fn () => $this->renderNodes($node['content'] ?? []);

        return match ($type) {
            'paragraph' => '<p>'.$contenuEnfants().'</p>',
            'heading' => $this->renderHeading($node, $contenuEnfants()),
            'bulletList' => '<ul>'.$contenuEnfants().'</ul>',
            'orderedList' => '<ol>'.$contenuEnfants().'</ol>',
            'listItem' => '<li>'.$contenuEnfants().'</li>',
            'blockquote' => '<blockquote>'.$contenuEnfants().'</blockquote>',
            'horizontalRule' => '<hr>',
            'hardBreak' => '<br>',
            'text' => $this->renderText($node),
            default => $contenuEnfants(),
        };
    }

    private function renderHeading(array $node, string $contenu): string
    {
        $niveau = min(max((int) ($node['attrs']['level'] ?? 2), 1), 6);

        return "<h{$niveau}>{$contenu}</h{$niveau}>";
    }

    private function renderText(array $node): string
    {
        $texte = e($node['text'] ?? '');

        foreach (($node['marks'] ?? []) as $marque) {
            $texte = match ($marque['type'] ?? null) {
                'bold' => "<strong>{$texte}</strong>",
                'italic' => "<em>{$texte}</em>",
                'strike' => "<s>{$texte}</s>",
                'code' => "<code>{$texte}</code>",
                default => $texte,
            };
        }

        return $texte;
    }
}
