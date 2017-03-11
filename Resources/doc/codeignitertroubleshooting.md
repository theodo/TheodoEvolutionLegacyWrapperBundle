#CodeIgniter Troubleshooting

- Symfony Debug Toolbar 404|500
    - add the following to your CodeIgniter `routes.php` config file:
    
    
        if (is_bool(strpos(ENVIRONMENT, 'prod')))
        {
            $route['404_override'] = 'nomEmptyStringValue';
        }
