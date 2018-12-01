<?php

namespace App\Controller;

use LightSaml\Model\Context\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/idp")
 */
class IdpController extends AbstractController {
    /**
     * @Route("", name="idp_details")
     */
    public function index() {
        $context = new SerializationContext();
        $own = $this->get('lightsaml.own.entity_descriptor_provider')
            ->get();
        $own->serialize($context->getDocument(), $context);
        $context->getDocument()->formatOutput = true;
        $idpXml = $context->getDocument()->saveXML();

        $cert = openssl_x509_read(file_get_contents($this->getParameter('kernel.project_dir') . '/certs/idp.crt'));
        $certificateInfo = openssl_x509_parse($cert);
        openssl_x509_free($cert);

        $loginUrl = $this->generateUrl('idp_saml');

        return $this->render('idp/index.html.twig', [
            'idpXml' => $idpXml,
            'loginUrl' => $loginUrl,
            'certificate' => $certificateInfo
        ]);
    }
}