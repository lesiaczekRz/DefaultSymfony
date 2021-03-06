<?php

/**
 * Kopia klasy LDAP z inną metodą bind
 */

namespace App\Security;

use Symfony\Component\Ldap\LdapInterface;
use Symfony\Component\Ldap\Adapter\QueryInterface;
use Symfony\Component\Ldap\Adapter\ExtLdap\Adapter;
use Symfony\Component\Ldap\Adapter\EntryManagerInterface;
use Symfony\Component\Ldap\Exception\DriverNotFoundException;

/**
 * @author Charles Sarrazin <charles@sarraz.in>
 */
final class Ldap implements LdapInterface
{
    private $adapter;

    private const ADAPTER_MAP = [
        'ext_ldap' => 'Symfony\Component\Ldap\Adapter\ExtLdap\Adapter',
    ];

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function bind(string $dn = null, string $password = null)
    {
        $payload = file_get_contents('php://input');
        if ($payload) {
            $data = \json_decode($payload, 1);
        }
        if (isset($_POST['username']) && isset($_POST['password']))
        {
            $dn = $_POST['username'];
            $password = $_POST['password'];
        } elseif (isset($data) && isset($data['username']) && isset($data['password'])) {
            $dn = $data['username'];
            $password = $data['password'];
        }
        $this->adapter->getConnection()->bind($dn, $password); 
    }

    /**
     * {@inheritdoc}
     */
    public function query(string $dn, string $query, array $options = []): QueryInterface
    {
        return $this->adapter->createQuery($dn, $query, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntryManager(): EntryManagerInterface
    {
        return $this->adapter->getEntryManager();
    }

    /**
     * {@inheritdoc}
     */
    public function escape(string $subject, string $ignore = '', int $flags = 0): string
    {
        return $this->adapter->escape($subject, $ignore, $flags);
    }

    /**
     * Creates a new Ldap instance.
     *
     * @param string $adapter The adapter name
     * @param array  $config  The adapter's configuration
     *
     * @return static
     */
    public static function create(string $adapter, array $config = []): self
    {
        if (!isset(self::ADAPTER_MAP[$adapter])) {
            throw new DriverNotFoundException(sprintf('Adapter "%s" not found. You should use one of: "%s".', $adapter, implode('", "', self::ADAPTER_MAP)));
        }

        $class = self::ADAPTER_MAP[$adapter];

        return new self(new $class($config));
    }
}
