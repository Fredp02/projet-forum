

/**
 * ********************************************************
 */
/**
 * Ce code permet de régler un problème : 
 * Si on a un message avec un ou de smiley, ces dernier sont affichées grace à des span qui ont une classe particuliere : ap ap-xxxxxx. Le problèle c'est que si je clique sur le bouton citation d'un de ces messages, l'objet quill récupère le contenu mais uniquement le text ! donc plus de balise span (+les classes) et donc plus d'émoticon ! Pourquoi ?
 * Parce que seule quelque balise html son acceptées dans la zone de l'éditeur de quill. voir ce liens pour plus de détails : (https://quilljs.com/docs/formats/)
 * Il faut donc spécifier à Quill quel autre format je souhaite intégrer dans l'éditeur, et donc des span. Ainsi les spans + leurs classe respectuives sont incorporée dans les blockquote et les emoticone apparaissent bien dans les citations
 */
const Inline = Quill.import('blots/inline');

// Créer une nouvelle classe de format pour les éléments span
class SpanBlot extends Inline {
    static create(value) {
        let node = super.create();
        node.setAttribute('class', value);
        return node;
    }

    static formats(node) {
        return node.getAttribute('class');
    }
}
SpanBlot.blotName = 'span';
SpanBlot.tagName = 'span';
// Enregistrer la nouvelle classe de format
Quill.register(SpanBlot);

/**
 * ****************************************************
 */

const toolbarOptions = {
    container: [
        ['bold', 'italic', 'underline', 'strike'],
        ['blockquote'],
        [{ 'header': 1 }, { 'header': 2 }],
        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
        [{ 'color': [] }, { 'background': [] }],
        ['clean'],
        ['emoji'],
        ['link', 'image']
    ],
    handlers: {
        'emoji': function () { }
    }
}

export const quill = new Quill('.editor', {
    modules: {
        "toolbar": toolbarOptions,
        "emoji-toolbar": true,
        "emoji-shortname": true,
        "emoji-textarea": false
    },
    placeholder: 'Écris ton message...',
    theme: 'snow',
});