<?php

return [
    'notifications' => [
        'created' => [
            'subject' => 'Novo ticket {{ticket_number}}',
            'greeting' => 'Ola,',
            'lines' => [
                'Foi criado um novo ticket no sistema.',
                'Numero: {{ticket_number}}',
                'Assunto: {{subject}}',
                'Mensagem: {{message}}',
            ],
        ],
        'reply' => [
            'subject' => 'Nova resposta no ticket {{ticket_number}}',
            'greeting' => 'Ola,',
            'lines' => [
                'O ticket {{ticket_number}} recebeu uma nova resposta.',
                'Mensagem: {{reply_message}}',
            ],
        ],
        'assigned' => [
            'subject' => 'Ticket atribuido {{ticket_number}}',
            'greeting' => 'Ola,',
            'lines' => [
                'Foi atribuido a si um ticket no sistema interno.',
                'Numero: {{ticket_number}}',
                'Assunto: {{subject}}',
                'Estado: {{status_name}}',
            ],
        ],
        'by_inbox' => [
            // Exemplo:
            // 'comercial' => [
            //     'created' => [
            //         'subject' => 'Comercial: novo ticket {{ticket_number}}',
            //         'greeting' => 'Ola equipa Comercial,',
            //         'lines' => [
            //             'Ticket recebido no departamento Comercial.',
            //             'Numero: {{ticket_number}}',
            //             'Assunto: {{subject}}',
            //         ],
            //     ],
            // ],
        ],
    ],
];
