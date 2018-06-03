<?php

namespace Drupal\user_api\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\user\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "user_api_reset_password",
 *   label = @Translation("Reset password"),
 *   uri_paths = {
 *     "create" = "/api/rest/user-api/reset-password/{user}"
 *   }
 * )
 */
class ResetPassword extends ResourceBase
{

    /**
     * A current user instance.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    /**
     * Constructs a new ResetPassword object.
     *
     * @param array $configuration
     *   A configuration array containing information about the plugin instance.
     * @param string $plugin_id
     *   The plugin_id for the plugin instance.
     * @param mixed $plugin_definition
     *   The plugin implementation definition.
     * @param array $serializer_formats
     *   The available serialization formats.
     * @param \Psr\Log\LoggerInterface $logger
     *   A logger instance.
     * @param \Drupal\Core\Session\AccountProxyInterface $current_user
     *   A current user instance.
     */
    public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        array $serializer_formats,
        LoggerInterface $logger,
        AccountProxyInterface $current_user)
    {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

        $this->currentUser = $current_user;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
    {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->getParameter('serializer.formats'),
            $container->get('logger.factory')->get('user_api'),
            $container->get('current_user')
        );
    }

    /**
     * Responds to POST requests.
     *
     * @param User $user
     * @param $data
     * @return \Drupal\rest\ModifiedResourceResponse
     *   The HTTP response object.
     *
     * @throws \Drupal\Core\Entity\EntityStorageException
     */
    public function post(User $user, $data)
    {

        // You must to implement the logic of your REST Resource here.
        // Use current user after pass authentication to validate access.
        if (!$this->currentUser->hasPermission('access content')) {
            throw new AccessDeniedHttpException();
        }

        if (isset($data['new_password']))
            $user->setPassword($data['new_password']);

        $user->save();

        return new ModifiedResourceResponse($user, 200);
    }

    /**
     * {@inheritdoc}
     */
    protected function getBaseRoute($canonical_path, $method) {
        $route = parent::getBaseRoute($canonical_path, $method);
        $parameters = $route->getOption('parameters') ?: [];
        $parameters['user']['type'] = 'entity:user';
        $route->setOption('parameters', $parameters);

        return $route;
    }
}
