<?php

/**
 * Контроллер пользователей
 */
class UsersControl extends PageControl {
    
    /**
     * @var array Допустимые действия
     */
    protected $actions = [
        'getUsersByRating',
        'addALotOfUsers'
    ];
    
    protected $fields = [
        'getUsersByRating' => [
            'search'        => ['text',     false,  255],
            'activity_id'   => ['number',   false,  0],
            'country'       => ['number',   false,  0],
            'city'          => ['number',   false,  0],
            'age'           => ['number',   false,  0,      8],
            'gender'        => ['oneOf',    false,  [
                                                        '0',
                                                        'M',
                                                        'W'
                                                    ]
                               ]
        ]
    ];


    /**
     * Конструктор. Определяет действие и запускает его
     */
    public function __construct() {
        parent::__construct();
        $users = new UsersModel();
        switch ($this->action):
            case 'getUsersByRating':
                if ($this->checkVars(isset($_POST['search']) ? $_POST : $_GET)) {
                    $data = $users->getUsersByRating( $this->vars, 'active', $this->per_page, $this->per_page * ($this->page - 1));
                    $this->view->json($data);
                }
                else {
                    $this->view->data->errors = $this->errors;
                    $this->view->error(400, 'users');
                }
            break;
            case 'addALotOfUsers':
                $users->addALotOfUsers();
            break;
        endswitch;
    }
}
