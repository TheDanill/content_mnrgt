<?php
class UsersEditControl extends AdminControl {
    
    protected   $actions = [
                    'show',
                    'add',
                    'edit',
                    'ban',
                    'trash',
                    'remove'
                ],
                $orders = [
                    'id',
                    'id_desc',
                    'name',
                    'name_desc',
                    'surname',
                    'surname_desc',
                    'birthday',
                    'birthday_desc',
                    'country',
                    'country_desc',
                    'gender',
                    'gender_desc',
                    'datetime',
                    'datetime_desc',
                    'rating',
                    'rating_desc',
                    'permissions',
                    'permissions_desc',
                    'activity',
                    'activity_desc',
                    'status',
                    'status_desc'
                ],
                $fields = [
                    
                ];
    
    private     $type,
                $types = [
                    'active' => 'Пользователи',
                    'trash' => 'Корзина',
                    'banned' => 'Забаненные пользователи'
                ],
                $manyVars = [],
                $manyErrors = [];

    public function __construct() {
        parent::__construct();
        if ($this->action) {
            $this->getOrder(true);
            $this->getType();
            $users = new UsersEditModel($this->page, $this->per_page, $this->order);
            switch ($this->action) {
                case 'show':
                    $this->showUsers($users);
                    break;
                case 'add':
                    if (isset($_POST['users']) && is_array($_POST['users'])) {
                        $this->checkUsers();
                        if (!$this->view->data->has_error) {
                            $this->view->data->added = $users->add($this->manyVars);
                        }
                        else {
                            $this->view->data->added = false;
                            $this->view->data->errors = $this->manyErrors;
                        }
                        $this->showUsers($users);
                    }
                    else
                        $this->view->error(400);
                    break;
                case 'ban':
                case 'trash':
                case 'remove':
                    if (isset($_POST['users']) && is_array($_POST['users'])) {
                        $this->checkUsers();
                        if (!$this->view->data->has_error) {
                            $this->view->data->updated = $users->{$this->action}(array_map('current', $this->manyVars));
                        }
                        else {
                            $this->view->data->updated = false;
                            $this->view->data->errors = $this->manyErrors;
                        }
                        $this->showUsers($users);
                    }
                    else
                        $this->view->error(400);
                    break;
                default:
                    $this->view->error(404);
                    break;
            }
        }
    }
    
    private function getType() {
        $this->type = isset($_GET['type']) && in_array($_GET['type'], array_keys($this->types)) ? $_GET['type'] : array_keys($this->types)[0];
    }
    
    private function checkUsers() {
        $this->view->data->has_error = false;
        foreach ($_POST['users'] as $key => $user) {
            if (!$this->checkVars($user, $this->manyVars[$key], $this->manyErrors[$key]))
                $this->view->data->has_error = true;
        }
    }
    
    private function showUsers(UsersEditModel $users) {
        $this->view->title = $this->types[$this->type];
        $this->view->data->type = $this->type;
        if ($users->getUsers($this->type)) {
            $this->view->data->users = $users->users;
            $this->view->data->found = $users->found;
            $countries = new CountryModel();
            $this->view->data->countries = $countries->getCountriesArrayID();
            $cities = new CityModel();
            $this->view->data->cities = $cities->getCitiesArrayID();
            $activities = new ActivityModel();
            $this->view->data->activities = $activities->getActivitiesArrayID();
            $this->view->page('usersedit');
        }
        else
            $this->view->error(404, 'usersedit');
    }
    
}