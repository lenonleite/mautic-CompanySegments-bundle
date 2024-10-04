<?php

namespace MauticPlugin\LeuchtfeuerCompanySegmentsBundle\Tests\Functional\Security\Permissions;

use Mautic\CoreBundle\Test\MauticMysqlTestCase;
use Mautic\UserBundle\Entity\User;
use MauticPlugin\LeuchtfeuerCompanySegmentsBundle\Entity\CompanySegment;
use MauticPlugin\LeuchtfeuerCompanySegmentsBundle\Tests\EnablePluginTrait;
use MauticPlugin\LeuchtfeuerCompanySegmentsBundle\Tests\NewUserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CompanySegmentsPermissionsTest extends MauticMysqlTestCase
{
    use EnablePluginTrait;
    use NewUserTrait;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->enablePlugin(true);
        $this->useCleanupRollback = false;
        $this->setUpSymfony($this->configParams);
    }

    private function newLogin(User $user, string $password): void
    {
        // Disable the default logging in via username and password.
        $this->clientServer = [];
        $this->setUpSymfony($this->configParams);
        $username = method_exists($user, 'getUserIdentifier') ? $user->getUserIdentifier() : $user->getUsername();
        $this->loginUser($username);
        $this->client->setServerParameter('PHP_AUTH_USER', $username);
        $this->client->setServerParameter('PHP_AUTH_PW', $password);
    }

    public function testUserWithPermissionToView(): void
    {
        $username    = 'usercompanysegments0';
        $password    = 'Maut1cR0cks!';
        $permissions = ['companysegment:companysegments' => ['viewown', 'viewother']];
        $user        = $this->newUserWithPermission($username, $password, false, $permissions);
        $this->newLogin($user, $password);
        $this->client->request(Request::METHOD_GET, '/api/companysegments');
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testUserWithoutPermissionToView(): void
    {
        $username    = 'usercompanysegments1';
        $password    = 'Maut1cR0cks!';
        $permissions = ['companysegment:companysegments' => []];
        $user        = $this->newUserWithPermission($username, $password, false, $permissions);
        $this->newLogin($user, $password);
        $this->client->request(Request::METHOD_GET, '/api/companysegments');
        self::assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testUserWithPermissionToViewOne(): void
    {
        $username    = 'usercompanysegments0';
        $password    = 'Maut1cR0cks!';
        $permissions = ['companysegment:companysegments' => ['viewown', 'viewother']];
        $user        = $this->newUserWithPermission($username, $password, false, $permissions);
        $this->newLogin($user, $password);
        $companySegment = $this->addCompanySegment('Segment test', 'segment-test', true);
        $this->client->request(Request::METHOD_GET, '/api/companysegments/'.$companySegment->getId());
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testUserWithoutPermissionToViewOne(): void
    {
        $username    = 'usercompanysegments1';
        $password    = 'Maut1cR0cks!';
        $permissions = ['companysegment:companysegments' => ['create']];
        $user        = $this->newUserWithPermission($username, $password, false, $permissions);
        $this->newLogin($user, $password);
        $companySegment = $this->addCompanySegment('Segment test', 'segment-test', true);
        $this->client->request(Request::METHOD_GET, '/api/companysegments/'.$companySegment->getId());
        self::assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testUserWithPermissionToSave(): void
    {
        $username    = 'usercompanysegments';
        $password    = 'Maut1cR0cks!';
        $permissions = ['companysegment:companysegments'=> ['create']];
        $user        = $this->newUserWithPermission($username, $password, false, $permissions);
        $this->newLogin($user, $password);

        $data = [
            'name'        => 'Segment test',
            'alias'       => 'segment-test-a',
            'isPublished' => '1',
        ];

        $this->client->request(Request::METHOD_POST, '/api/companysegments/new', $data);
        self::assertSame(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
    }

    public function testUserWithoutPermissionToSave(): void
    {
        $username    = 'usercompanysegments2';
        $password    = 'Maut1cR0cks!';
        $permissions = ['companysegment:companysegments' => ['viewown', 'viewother']];
        $user        = $this->newUserWithPermission($username, $password, false, $permissions);
        $this->newLogin($user, $password);
        $this->client->request(Request::METHOD_POST, '/api/companysegments/new', []);
        self::assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testUserWithPermissionToEdit(): void
    {
        $username    = 'usercompanysegments3';
        $password    = 'Maut1cR0cks!';
        $permissions = ['companysegment:companysegments' => ['editown', 'editother']];
        $user        = $this->newUserWithPermission($username, $password, false, $permissions);
        $this->newLogin($user, $password);
        $companySegment = $this->addCompanySegment('Segment test', 'segment-test', true);
        $data           = [
            'name'        => 'Segment test edited',
            'alias'       => 'segment-test-a',
            'isPublished' => '1',
            'id'          => $companySegment->getId(),
        ];
        $this->client->request(Request::METHOD_PATCH, '/api/companysegments/'.$companySegment->getId().'/edit', $data);
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testUserWithoutPermissionToEdit(): void
    {
        $username    = 'usercompanysegments4';
        $password    = 'Maut1cR0cks!';
        $permissions = ['companysegment:companysegments' => ['viewown', 'viewother']];
        $user        = $this->newUserWithPermission($username, $password, false, $permissions);
        $this->newLogin($user, $password);
        $companySegment = $this->addCompanySegment('Segment test', 'segment-test', true);
        $data           = [
            'name'        => 'Segment test edited',
            'alias'       => 'segment-test-a',
            'isPublished' => '1',
            'id'          => $companySegment->getId(),
        ];
        $this->client->request(Request::METHOD_PATCH, '/api/companysegments/'.$companySegment->getId().'/edit', $data);
        self::assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testUserWithPermissionToDelete(): void
    {
        $username    = 'usercompanysegments5';
        $password    = 'Maut1cR0cks!';
        $permissions = ['companysegment:companysegments' => ['deleteown', 'deleteother']];
        $user        = $this->newUserWithPermission($username, $password, false, $permissions);
        $this->newLogin($user, $password);
        $companySegment = $this->addCompanySegment('Segment test', 'segment-test', true);
        $this->client->request(Request::METHOD_DELETE, '/api/companysegments/'.$companySegment->getId().'/delete');
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testUserWithoutPermissionToDelete(): void
    {
        $username    = 'usercompanysegments6';
        $password    = 'Maut1cR0cks!';
        $permissions = ['companysegment:companysegments' => ['viewown', 'viewother']];
        $user        = $this->newUserWithPermission($username, $password, false, $permissions);
        $this->newLogin($user, $password);
        $companySegment = $this->addCompanySegment('Segment test', 'segment-test', true);
        $this->client->request(Request::METHOD_DELETE, '/api/companysegments/'.$companySegment->getId().'/delete');
        self::assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testUserWithPermissionToBatchAddCompaniesSegments(): void
    {
        $username    = 'usercompanysegments7';
        $password    = 'Maut1cR0cks!';
        $permissions = ['companysegment:companysegments' => ['create']];
        $user        = $this->newUserWithPermission($username, $password, false, $permissions);
        $this->newLogin($user, $password);
        $data = [
            [
                'name'        => 'Segment test edited',
                'alias'       => 'segment-test-a',
                'isPublished' => '1',
            ],
            [
                'name'        => 'Segment test 2 edited',
                'alias'       => 'segment-test-2-a',
                'isPublished' => '1',
            ],
        ];
        $this->client->request(Request::METHOD_POST, '/api/companysegments/batch/new', $data);
        self::assertSame(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
    }

    public function testUserWithoutPermissionToBatchAddCompaniesSegments(): void
    {
        $username    = 'usercompanysegments8';
        $password    = 'Maut1cR0cks!';
        $permissions = ['companysegment:companysegments' => ['viewown', 'viewother']];
        $user        = $this->newUserWithPermission($username, $password, false, $permissions);
        $this->newLogin($user, $password);
        $data = [
            [
                'name'        => 'Segment test edited',
                'alias'       => 'segment-test-a',
                'isPublished' => '1',
            ],
            [
                'name'        => 'Segment test 2 edited',
                'alias'       => 'segment-test-2-a',
                'isPublished' => '1',
            ],
        ];
        $this->client->request(Request::METHOD_POST, '/api/companysegments/batch/new', $data);
        self::assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testUserWithPermissionToBatchEditFailCompaniesSegments(): void
    {
        $username    = 'usercompanysegments9';
        $password    = 'Maut1cR0cks!';
        $permissions = ['companysegment:companysegments' => []];
        $user        = $this->newUserWithPermission($username, $password, false, $permissions);
        $this->newLogin($user, $password);
        $companySegment = $this->addCompanySegment('Segment test', 'segment-test', true);
        $data           = [
            [
                'id'          => $companySegment->getId(),
                'name'        => 'Segment test edited',
                'alias'       => 'segment-test-a',
                'isPublished' => '1',
            ],
        ];
        $this->client->request(Request::METHOD_PATCH, '/api/companysegments/batch/edit', $data);
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        self::assertNotFalse($this->client->getResponse()->getContent());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertIsArray($response);
        self::assertIsArray($response['statusCodes']);
        self::assertSame(Response::HTTP_FORBIDDEN, $response['statusCodes'][0]);
    }

    private function addCompanySegment(string $name, string $alias, bool $isPublished = true): CompanySegment
    {
        $companySegment = new CompanySegment();
        $companySegment->setName($name);
        $companySegment->setAlias($alias);
        $companySegment->setIsPublished($isPublished);
        $this->em->persist($companySegment);
        $this->em->flush();

        return $companySegment;
    }
}
