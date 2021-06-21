<?php

declare(strict_types=1);

namespace PHPImageBank\tests\App;

use PHPImageBank\tests\Selenium\Helpers\SeleniumTestCase;
use PHPImageBank\Models\Image;
use PHPImageBank\Models\Tag;
use PHPImageBank\Models\ImageTag;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\LocalFileDetector;

final class ImageTest extends SeleniumTestCase
{
    public function testCreate()
    {
        $data = [];
        $data['username'] = "admin";

        $this->signIn($data);

        $this->driver->get($this->website . '/upload');
        $this->driver->findElement(WebDriverBy::id('imagename'))
            ->sendKeys("Test image");
        $this->driver->findElement(WebDriverBy::id('description'))
            ->sendKeys("Test description");
        $this->driver->findElement(WebDriverBy::id('tags'))
            ->sendKeys("tag_a, tag_b");

        $fileInput = $this->driver->findElement(WebDriverBy::id('imagefile'));
        $fileInput->setFileDetector(new LocalFileDetector());

        $fileInput->sendKeys(__DIR__ . "/../../public/images/testimage.jpg")
            ->submit();

        $newurl = $this->driver->getCurrentURL();
        $this->assertStringContainsString("/images/", $newurl);

        $data['image_id'] = explode("/images/", $newurl)[1];
        $data['link'] = $newurl;

        return $data;
    }

    /**
     * @depends testCreate
     */
    public function testView($data)
    {
        $this->driver->get($data['link']);
        $title = $this->driver->findElement(
            WebDriverBy::xpath('/html/body/div[1]/h3')
        );
        $this->assertStringContainsString("- Test image", $title->getText());
        return $data;
    }

    /**
     * @depends testView
     */
    public function testComment($data)
    {
        $this->signIn($data);

        $this->driver->get($data['link']);
        $this->driver->findElement(WebDriverBy::name('comment'))
            ->sendKeys("Test comment")
            ->submit();
        
        $comment = $this->driver->findElement(
            WebDriverBy::cssSelector('.comments > div > div.cm-column.cm-body')
        );
        $this->assertStringContainsString("Test comment", $comment->getText());
        
        return $data;
    }

    /**
     * @depends testComment
     */
    public function testCategory($data)
    {
        $tag_b = Tag::getByField('tagname', 'tag_b')->one();
        $tag_c = Tag::getByField('tagname', 'tag_c')->one();

        $image_bc = Image::fromRow(["imagename" => "Test image", "filename" => "testimage.jpg",  "description" => "Test description", "uploader_id" => 1]);
        $image_bc->save();
        $data['image_bc_id'] = $image_bc->id;

        $imagetag_b = ImageTag::fromRow(["image_id" => $image_bc->id, "tag_id" => $tag_b->id]);
        $imagetag_b->save();
        $imagetag_c = ImageTag::fromRow(["image_id" => $image_bc->id, "tag_id" => $tag_c->id]);
        $imagetag_c->save();

        $this->driver->get($this->website . '/?tags=tag_a');
        $image_a_link = $this->driver->findElement(WebDriverBy::cssSelector('.thumb-link'))->getattribute('href');
        $this->assertEquals($data['link'], $this->website . $image_a_link);

        $this->driver->get($this->website . '/?tags=tag_c');
        $image_bc_link = $this->driver->findElement(WebDriverBy::cssSelector('.thumb-link'))->getattribute('href');
        $this->assertEquals($this->website . '/images/' . $data['image_bc_id'], $this->website . $image_bc_link);
        
        $this->driver->get($this->website . '/?tags=tag_b');
        $images = $this->driver->findElements(WebDriverBy::cssSelector('.thumb-link'));
        $links = [];
        foreach($images as $img) {
            array_push($links, $img->getattribute('href'));
        }
        $this->assertContains("/images/" . $data["image_id"], $links);
        $this->assertContains("/images/" . $data["image_bc_id"], $links);

        return $data;
    }

    /**
     * @depends testCategory
     */
    public function testDelete($data)
    {
        $this->signIn($data);

        $e = explode("/", $data['link']);
        array_splice($e, 4, 0, ["delete"]);
        $this->driver->get(implode("/", $e));

        $this->driver->get($data['link']);
        $info = $this->driver->findElement(
            WebDriverBy::cssSelector('.content > h3')
        );
        $this->assertStringContainsString("Image not found", $info->getText());

        $this->driver->get($this->website . "/images/delete/" . $data["image_bc_id"]);
    }
}